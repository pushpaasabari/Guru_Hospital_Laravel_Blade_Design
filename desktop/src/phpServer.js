import crypto from 'node:crypto';
import { execFileSync, spawn } from 'node:child_process';
import fs from 'node:fs';
import http from 'node:http';
import net from 'node:net';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const ROOT_DIR = path.resolve(__dirname, '..', '..');

export class OfflinePhpServer {
  constructor(electronApp) {
    this.electronApp = electronApp;
    this.process = null;
    this.schedulerTimer = null;
    this.port = null;
    this.url = null;
  }

  resolvePaths() {
    if (this.electronApp.isPackaged) {
      const resources = process.resourcesPath;
      const chromiumHeadless = path.join(resources, 'chromium', 'chrome-headless-shell.exe');
      const chromiumFull = path.join(resources, 'chromium', 'chrome.exe');
      return {
        appRoot: path.join(resources, 'app'),
        phpExe: path.join(resources, 'php', 'php.exe'),
        phpIni: path.join(resources, 'php', 'php.ini'),
        caFile: path.join(resources, 'php', 'cacert.pem'),
        chromiumExe: fs.existsSync(chromiumHeadless)
          ? chromiumHeadless
          : fs.existsSync(chromiumFull)
          ? chromiumFull
          : null,
      };
    }

    return {
      appRoot: ROOT_DIR,
      phpExe: fs.existsSync('C:\\xampp\\php\\php.exe') ? 'C:\\xampp\\php\\php.exe' : 'php',
      phpIni: null,
      caFile: null,
      chromiumExe: null,
    };
  }

  buildPhpIniArgs(paths) {
    const args = [];
    if (paths.phpIni) {
      args.push('-c', paths.phpIni);
    }
    const caFile = paths.caFile || (paths.phpIni ? path.join(path.dirname(paths.phpIni), 'cacert.pem') : null);
    if (caFile && fs.existsSync(caFile)) {
      args.push('-d', `curl.cainfo=${caFile}`, '-d', `openssl.cafile=${caFile}`);
    }
    return args;
  }

  resolveDataDir() {
    if (process.env.PORTABLE_EXECUTABLE_DIR) {
      const portableData = path.join(process.env.PORTABLE_EXECUTABLE_DIR, 'GuruHospitalData');
      if (fs.existsSync(portableData)) {
        return portableData;
      }
    }
    return this.electronApp.getPath('userData');
  }

  buildPhpEnv(dataDir, port, chromiumExe) {
    const dbFile = path.join(dataDir, 'database', 'database.sqlite');
    const storageDir = path.join(dataDir, 'storage');
    const cacheDir = path.join(dataDir, 'cache');

    const env = {
      ...process.env,
      APP_NAME: 'Guru Hospital',
      APP_ENV: 'production',
      APP_DEBUG: 'false',
      APP_URL: `http://127.0.0.1:${port}`,
      DB_CONNECTION: 'sqlite',
      DB_DATABASE: dbFile,
      SESSION_DRIVER: 'file',
      SESSION_ENCRYPT: 'false',
      SESSION_SECURE_COOKIE: 'false',
      QUEUE_CONNECTION: 'sync',
      CACHE_STORE: 'file',
      LOG_CHANNEL: 'daily',
      LARAVEL_ENV_PATH: dataDir,
      LARAVEL_STORAGE_PATH: storageDir,
      APP_SERVICES_CACHE: path.join(cacheDir, 'services.php'),
      APP_PACKAGES_CACHE: path.join(cacheDir, 'packages.php'),
      APP_CONFIG_CACHE: path.join(cacheDir, 'config.php'),
      APP_ROUTES_CACHE: path.join(cacheDir, 'routes-v7.php'),
      APP_EVENTS_CACHE: path.join(cacheDir, 'events.php'),
      NODE_BINARY: process.execPath,
      ELECTRON_RUN_AS_NODE: '1',
    };

    if (chromiumExe && fs.existsSync(chromiumExe)) {
      env.PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH = chromiumExe;
    }

    return env;
  }

  ensureInitialized(paths, dataDir, port, onStatus) {
    const storageDir = path.join(dataDir, 'storage');
    const cacheDir = path.join(dataDir, 'cache');
    const dbDir = path.join(dataDir, 'database');
    const dbFile = path.join(dbDir, 'database.sqlite');
    const envFile = path.join(dataDir, '.env');
    const versionFile = path.join(dataDir, '.migrated-version');

    for (const dir of [
      dbDir,
      cacheDir,
      path.join(storageDir, 'app/private/backups'),
      path.join(storageDir, 'app/private/documents'),
      path.join(storageDir, 'app/private/temp_pdf'),
      path.join(storageDir, 'app/public'),
      path.join(storageDir, 'framework/cache/data'),
      path.join(storageDir, 'framework/sessions'),
      path.join(storageDir, 'framework/views'),
      path.join(storageDir, 'logs'),
    ]) {
      fs.mkdirSync(dir, { recursive: true });
    }

    if (!fs.existsSync(envFile)) {
      const appKey = `base64:${crypto.randomBytes(32).toString('base64')}`;
      const envContent = [
        'APP_NAME="Guru Hospital"',
        'APP_ENV=production',
        `APP_KEY=${appKey}`,
        'APP_DEBUG=false',
        `APP_URL=http://127.0.0.1:${port}`,
        'APP_LOCALE=en',
        'LOG_CHANNEL=daily',
        'LOG_LEVEL=warning',
        'DB_CONNECTION=sqlite',
        `DB_DATABASE="${dbFile.replace(/\\/g, '/')}"`,
        'SESSION_DRIVER=file',
        'SESSION_LIFETIME=120',
        'SESSION_ENCRYPT=false',
        'SESSION_SECURE_COOKIE=false',
        'QUEUE_CONNECTION=sync',
        'CACHE_STORE=file',
        'DB_BACKUP_ENABLED=true',
        'DB_BACKUP_TIME=02:00',
        'DB_BACKUP_TIMEZONE=Asia/Kolkata',
        'DB_BACKUP_LOCAL_RETENTION_DAYS=7',
        'DB_BACKUP_DRIVE_RETENTION_DAYS=30',
      ].join('\n');
      fs.writeFileSync(envFile, envContent + '\n', 'utf8');
    }

    const isFreshDb = !fs.existsSync(dbFile) || fs.statSync(dbFile).size === 0;
    if (!fs.existsSync(dbFile)) {
      fs.writeFileSync(dbFile, '');
    }

    const env = this.buildPhpEnv(dataDir, port, paths.chromiumExe);
    const appVersion = this.electronApp.getVersion();
    const migratedVersion = fs.existsSync(versionFile) ? fs.readFileSync(versionFile, 'utf8').trim() : '';

    if (isFreshDb || migratedVersion !== appVersion) {
      onStatus?.('Preparing offline hospital database & schema migrations...');
      const phpBaseArgs = this.buildPhpIniArgs(paths);
      execFileSync(paths.phpExe, [...phpBaseArgs, 'artisan', 'migrate', '--force'], {
        cwd: paths.appRoot,
        env,
        stdio: 'pipe',
      });

      const countCheck = execFileSync(
        paths.phpExe,
        [
          ...phpBaseArgs,
          '-r',
          `$p=new PDO('sqlite:'.getenv('DB_DATABASE')); echo (int)$p->query('SELECT COUNT(*) FROM users')->fetchColumn();`,
        ],
        { cwd: paths.appRoot, env, encoding: 'utf8' }
      ).trim();

      if (countCheck === '0') {
        onStatus?.('Seeding initial hospital staff & clinical directories...');
        execFileSync(paths.phpExe, [...phpBaseArgs, 'artisan', 'db:seed', '--force'], {
          cwd: paths.appRoot,
          env,
          stdio: 'pipe',
        });
      }

      fs.writeFileSync(versionFile, appVersion, 'utf8');
    }

    return env;
  }

  async findFreePort() {
    return new Promise((resolve, reject) => {
      const server = net.createServer();
      server.unref();
      server.on('error', reject);
      server.listen(0, '127.0.0.1', () => {
        const addr = server.address();
        const port = typeof addr === 'object' && addr ? addr.port : 18745;
        server.close(() => resolve(port));
      });
    });
  }

  async waitUntilReady(url, timeoutMs = 20000) {
    const start = Date.now();
    while (Date.now() - start < timeoutMs) {
      try {
        const ok = await new Promise((resolve) => {
          const req = http.get(`${url}/up`, { timeout: 1000 }, (res) => {
            res.resume();
            resolve(res.statusCode >= 200 && res.statusCode < 500);
          });
          req.on('error', () => resolve(false));
          req.on('timeout', () => {
            req.destroy();
            resolve(false);
          });
        });
        if (ok) return;
      } catch {
        // Retry
      }
      await new Promise((r) => setTimeout(r, 200));
    }
    throw new Error('Offline PHP server failed to start within timeout.');
  }

  async start(onStatus) {
    if (this.process && this.url) {
      return this.url;
    }

    const paths = this.resolvePaths();
    const dataDir = this.resolveDataDir();
    const port = await this.findFreePort();
    const env = this.ensureInitialized(paths, dataDir, port, onStatus);

    onStatus?.(`Starting local clinical server on port ${port}...`);

    const router = path.join(
      paths.appRoot,
      'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'
    );
    const args = [...this.buildPhpIniArgs(paths), '-S', `127.0.0.1:${port}`, router];

    const logStream = fs.createWriteStream(
      path.join(dataDir, 'storage/logs/desktop-server.log'),
      { flags: 'a' }
    );

    const child = spawn(paths.phpExe, args, {
      cwd: path.join(paths.appRoot, 'public'),
      env,
      windowsHide: true,
      stdio: ['ignore', 'pipe', 'pipe'],
    });

    child.stdout?.pipe(logStream);
    child.stderr?.pipe(logStream);

    this.process = child;
    this.port = port;
    this.url = `http://127.0.0.1:${port}`;

    await this.waitUntilReady(this.url);

    // Run Laravel scheduler every 60s in background so daily 02:00 AM backups fire when open
    this.schedulerTimer = setInterval(() => {
      const schedArgs = [...this.buildPhpIniArgs(paths), 'artisan', 'schedule:run'];
      const sched = spawn(paths.phpExe, schedArgs, {
        cwd: paths.appRoot,
        env,
        windowsHide: true,
        stdio: 'ignore',
      });
      sched.unref();
    }, 60 * 1000);

    return this.url;
  }

  async runManualBackup() {
    const paths = this.resolvePaths();
    const dataDir = this.resolveDataDir();
    const port = this.port || 8000;
    const env = this.buildPhpEnv(dataDir, port, paths.chromiumExe);
    const phpBaseArgs = this.buildPhpIniArgs(paths);

    return execFileSync(paths.phpExe, [...phpBaseArgs, 'artisan', 'db:backup'], {
      cwd: paths.appRoot,
      env,
      encoding: 'utf8',
    });
  }

  stop() {
    if (this.schedulerTimer) {
      clearInterval(this.schedulerTimer);
      this.schedulerTimer = null;
    }
    if (this.process && !this.process.killed) {
      try {
        this.process.kill();
      } catch {
        // Ignore
      }
      this.process = null;
    }
    this.url = null;
  }
}
