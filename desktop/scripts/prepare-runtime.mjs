import { execFileSync, execSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const DESKTOP_DIR = path.resolve(__dirname, '..');
const ROOT_DIR = path.resolve(DESKTOP_DIR, '..');
const BUILD_DIR = path.join(DESKTOP_DIR, 'build');
const ASSETS_DIR = path.join(DESKTOP_DIR, 'assets');

function log(msg) {
  console.log(`[prepare-runtime] ${msg}`);
}

function ensureEmptyDir(dir) {
  fs.rmSync(dir, { recursive: true, force: true });
  fs.mkdirSync(dir, { recursive: true });
}

function copyFiltered(src, dest, filterFn = () => true) {
  if (!fs.existsSync(src)) return;
  const stat = fs.statSync(src);
  if (stat.isDirectory()) {
    if (!filterFn(src, true)) return;
    fs.mkdirSync(dest, { recursive: true });
    for (const entry of fs.readdirSync(src)) {
      copyFiltered(path.join(src, entry), path.join(dest, entry), filterFn);
    }
  } else {
    if (!filterFn(src, false)) return;
    fs.mkdirSync(path.dirname(dest), { recursive: true });
    fs.copyFileSync(src, dest);
  }
}

function findPhpDir() {
  const candidates = [];
  try {
    const out = execSync('php -r "echo dirname(PHP_BINARY);"', { encoding: 'utf8' }).trim();
    if (out) candidates.push(out);
  } catch {
    // Ignore
  }
  candidates.push('C:\\xampp\\php', 'C:\\php');

  for (const dir of candidates) {
    if (dir && fs.existsSync(path.join(dir, 'php.exe'))) {
      return dir;
    }
  }
  throw new Error('Unable to locate Windows php.exe runtime directory.');
}

function findCaBundle(phpDir) {
  const candidates = [
    path.join(phpDir, 'extras', 'ssl', 'cacert.pem'),
    path.join(phpDir, 'cacert.pem'),
    path.resolve(phpDir, '..', 'apache', 'bin', 'curl-ca-bundle.crt'),
    path.join(ROOT_DIR, 'vendor', 'composer', 'ca-bundle', 'res', 'cacert.pem'),
  ];
  return candidates.find((p) => fs.existsSync(p)) || null;
}

function findPlaywrightHeadlessShellDir() {
  const cacheDirs = [];
  if (process.env.PLAYWRIGHT_BROWSERS_PATH && process.env.PLAYWRIGHT_BROWSERS_PATH !== '0') {
    cacheDirs.push(process.env.PLAYWRIGHT_BROWSERS_PATH);
  }
  if (process.env.LOCALAPPDATA) {
    cacheDirs.push(path.join(process.env.LOCALAPPDATA, 'ms-playwright'));
  }
  cacheDirs.push(path.join(os.homedir(), 'AppData', 'Local', 'ms-playwright'));

  for (const cacheDir of cacheDirs) {
    if (!fs.existsSync(cacheDir)) continue;
    const dirs = fs
      .readdirSync(cacheDir, { withFileTypes: true })
      .filter((d) => d.isDirectory() && /^chromium_headless_shell-/.test(d.name))
      .map((d) => d.name)
      .sort((a, b) => (parseInt(b.replace(/\D+/g, ''), 10) || 0) - (parseInt(a.replace(/\D+/g, ''), 10) || 0));

    for (const dirName of dirs) {
      const win64 = path.join(cacheDir, dirName, 'chrome-headless-shell-win64');
      if (fs.existsSync(path.join(win64, 'chrome-headless-shell.exe'))) {
        return win64;
      }
      const win = path.join(cacheDir, dirName, 'chrome-headless-shell-win');
      if (fs.existsSync(path.join(win, 'chrome-headless-shell.exe'))) {
        return win;
      }
    }
  }

  // Fallback to full chromium directory if headless_shell wasn't installed separately
  for (const cacheDir of cacheDirs) {
    if (!fs.existsSync(cacheDir)) continue;
    const dirs = fs
      .readdirSync(cacheDir, { withFileTypes: true })
      .filter((d) => d.isDirectory() && /^chromium-/.test(d.name))
      .map((d) => d.name)
      .sort((a, b) => (parseInt(b.replace(/\D+/g, ''), 10) || 0) - (parseInt(a.replace(/\D+/g, ''), 10) || 0));

    for (const dirName of dirs) {
      const win64 = path.join(cacheDir, dirName, 'chrome-win64');
      if (fs.existsSync(path.join(win64, 'chrome.exe'))) {
        return win64;
      }
    }
  }

  throw new Error('Playwright Chromium browser not found in ms-playwright cache. Run "npx playwright install chromium" first.');
}

function generateIcons(phpExe) {
  log('Generating 256x256 application icons (PNG & ICO)...');
  fs.mkdirSync(ASSETS_DIR, { recursive: true });

  const emblemSrc = path.join(ROOT_DIR, 'public', 'images', 'guru-logo-emblem.png');
  const outPng = path.join(ASSETS_DIR, 'icon.png');
  const outIco = path.join(ASSETS_DIR, 'icon.ico');

  const phpCode = `
    $src = imagecreatefrompng(${JSON.stringify(emblemSrc)});
    if (!$src) { exit(1); }
    $dst = imagecreatetruecolor(256, 256);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
    imagefilledrectangle($dst, 0, 0, 256, 256, $transparent);
    $w = imagesx($src);
    $h = imagesy($src);
    // Subtle circular/rounded padding so logo breathes in Windows taskbar & shortcuts
    $pad = 14;
    imagecopyresampled($dst, $src, $pad, $pad, 0, 0, 256 - ($pad * 2), 256 - ($pad * 2), $w, $h);
    imagepng($dst, ${JSON.stringify(outPng)}, 9);
    imagedestroy($src);
    imagedestroy($dst);
  `;

  execFileSync(phpExe, ['-d', 'extension=gd', '-r', phpCode], { stdio: 'inherit' });

  const pngBytes = fs.readFileSync(outPng);
  const icoHeader = Buffer.alloc(22);
  icoHeader.writeUInt16LE(0, 0); // reserved
  icoHeader.writeUInt16LE(1, 2); // type: icon
  icoHeader.writeUInt16LE(1, 4); // count: 1 image
  icoHeader.writeUInt8(0, 6); // width 256 -> 0
  icoHeader.writeUInt8(0, 7); // height 256 -> 0
  icoHeader.writeUInt8(0, 8); // color count
  icoHeader.writeUInt8(0, 9); // reserved
  icoHeader.writeUInt16LE(1, 10); // planes
  icoHeader.writeUInt16LE(32, 12); // bpp
  icoHeader.writeUInt32LE(pngBytes.length, 14); // bytes in res
  icoHeader.writeUInt32LE(22, 18); // offset

  fs.writeFileSync(outIco, Buffer.concat([icoHeader, pngBytes]));
}

function stagePhpRuntime(phpDir, targetPhpDir) {
  log(`Staging portable PHP runtime from ${phpDir}...`);
  ensureEmptyDir(targetPhpDir);
  fs.mkdirSync(path.join(targetPhpDir, 'ext'), { recursive: true });

  const rootPatterns = [
    /^php\.exe$/i,
    /^php8ts\.dll$/i,
    /^icudt.*\.dll$/i,
    /^icuin.*\.dll$/i,
    /^icuio.*\.dll$/i,
    /^icuuc.*\.dll$/i,
    /^libcrypto.*\.dll$/i,
    /^libssl.*\.dll$/i,
    /^libsqlite3\.dll$/i,
    /^libsodium\.dll$/i,
    /^libssh2\.dll$/i,
    /^nghttp2\.dll$/i,
    /^glib-2\.dll$/i,
    /^gmodule-2\.dll$/i,
    /^libpq\.dll$/i,
    /^libsasl\.dll$/i,
  ];

  for (const file of fs.readdirSync(phpDir)) {
    if (rootPatterns.some((rx) => rx.test(file))) {
      fs.copyFileSync(path.join(phpDir, file), path.join(targetPhpDir, file));
    }
  }

  const requiredExtensions = [
    'php_curl.dll',
    'php_exif.dll',
    'php_fileinfo.dll',
    'php_gd.dll',
    'php_intl.dll',
    'php_mbstring.dll',
    'php_mysqli.dll',
    'php_opcache.dll',
    'php_openssl.dll',
    'php_pdo_mysql.dll',
    'php_pdo_sqlite.dll',
    'php_sodium.dll',
    'php_sqlite3.dll',
    'php_zip.dll',
  ];

  const srcExtDir = path.join(phpDir, 'ext');
  for (const ext of requiredExtensions) {
    const src = path.join(srcExtDir, ext);
    if (fs.existsSync(src)) {
      fs.copyFileSync(src, path.join(targetPhpDir, 'ext', ext));
    }
  }

  const caBundle = findCaBundle(phpDir);
  let caConfig = '';
  if (caBundle) {
    fs.copyFileSync(caBundle, path.join(targetPhpDir, 'cacert.pem'));
    caConfig = `curl.cainfo="cacert.pem"\nopenssl.cafile="cacert.pem"\n`;
  }

  const phpIni = `[PHP]
engine = On
short_open_tag = Off
precision = 14
output_buffering = 4096
zlib.output_compression = Off
implicit_flush = Off
expose_php = Off
max_execution_time = 180
max_input_time = 120
memory_limit = 512M
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
log_errors = On
post_max_size = 64M
upload_max_filesize = 64M
default_charset = "UTF-8"
extension_dir = "ext"
date.timezone = "Asia/Kolkata"

zend_extension=opcache
extension=curl
extension=exif
extension=fileinfo
extension=gd
extension=intl
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=sodium
extension=sqlite3
extension=zip

${caConfig}
[opcache]
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=16000
opcache.validate_timestamps=0
`;

  fs.writeFileSync(path.join(targetPhpDir, 'php.ini'), phpIni, 'utf8');
}

function stageLaravelApp(targetAppDir) {
  log('Staging production Laravel application...');
  ensureEmptyDir(targetAppDir);

  const dirsToCopy = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'vendor'];
  for (const rel of dirsToCopy) {
    copyFiltered(path.join(ROOT_DIR, rel), path.join(targetAppDir, rel), (srcPath, isDir) => {
      const normalized = srcPath.replace(/\\/g, '/');
      if (isDir && normalized.endsWith('/bootstrap/cache')) return false;
      if (!isDir && normalized.endsWith('/public/hot')) return false;
      if (!isDir && /\/database\/database\.sqlite/i.test(normalized)) return false;
      if (isDir && normalized.endsWith('/database/factories')) return false;
      return true;
    });
  }

  for (const file of ['artisan', 'composer.json', 'composer.lock', 'package.json']) {
    const src = path.join(ROOT_DIR, file);
    if (fs.existsSync(src)) {
      fs.copyFileSync(src, path.join(targetAppDir, file));
    }
  }

  // Prune Composer dev dependencies inside staged copy without touching root workspace
  try {
    log('Optimizing production Composer autoloader inside staged app...');
    execSync('composer install --no-dev --optimize-autoloader --no-interaction --no-scripts', {
      cwd: targetAppDir,
      stdio: 'inherit',
    });
  } catch (err) {
    log(`Composer production prune skipped (${err.message}); keeping copied vendor/.`);
  }

  // Copy only Playwright runtime needed by resources/scripts/generate-pdf.js
  for (const pkg of ['playwright', 'playwright-core']) {
    const srcPkg = path.join(ROOT_DIR, 'node_modules', pkg);
    if (fs.existsSync(srcPkg)) {
      copyFiltered(srcPkg, path.join(targetAppDir, 'node_modules', pkg));
    }
  }

  // Create minimal empty writable directories so Laravel helpers never complain before redirect
  for (const rel of [
    'bootstrap/cache',
    'storage/app/private/backups',
    'storage/app/private/documents',
    'storage/app/private/temp_pdf',
    'storage/app/public',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
  ]) {
    fs.mkdirSync(path.join(targetAppDir, rel), { recursive: true });
  }
}

function stageChromium(targetChromiumDir) {
  const srcDir = findPlaywrightHeadlessShellDir();
  log(`Staging bundled Chromium headless binary from ${srcDir}...`);
  ensureEmptyDir(targetChromiumDir);
  copyFiltered(srcDir, targetChromiumDir);
}

function main() {
  const phpDir = findPhpDir();
  const phpExe = path.join(phpDir, 'php.exe');

  log('Building production Vite frontend assets...');
  execSync('npm run build', { cwd: ROOT_DIR, stdio: 'inherit' });

  ensureEmptyDir(BUILD_DIR);
  generateIcons(phpExe);
  stagePhpRuntime(phpDir, path.join(BUILD_DIR, 'php'));
  stageLaravelApp(path.join(BUILD_DIR, 'app'));
  stageChromium(path.join(BUILD_DIR, 'chromium'));

  log('Desktop runtime preparation complete!');
}

main();
