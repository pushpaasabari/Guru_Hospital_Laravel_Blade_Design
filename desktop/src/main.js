import electron from 'electron';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { OfflinePhpServer } from './phpServer.js';

const { app, BrowserWindow, dialog, ipcMain, Menu, net, shell, Tray } = electron;

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const DEFAULT_LIVE_URL = 'https://guru.aifilecafe.com';

let mainWindow = null;
let tray = null;
let phpServer = null;
let currentMode = null; // 'offline' | 'live' | null

const isSmokeTest = process.argv.includes('--desktop-smoke-test');
const smokeDataArg = process.argv.find((arg) => arg.startsWith('--smoke-user-data='));
if (smokeDataArg) {
  const smokeDir = smokeDataArg.slice('--smoke-user-data='.length).trim();
  if (smokeDir) {
    fs.mkdirSync(smokeDir, { recursive: true });
    app.setPath('userData', smokeDir);
  }
}

const gotLock = isSmokeTest ? true : app.requestSingleInstanceLock();
if (!gotLock) {
  app.quit();
} else {
  app.on('second-instance', () => {
    if (mainWindow) {
      if (mainWindow.isMinimized()) mainWindow.restore();
      mainWindow.show();
      mainWindow.focus();
    }
  });
}

function getConfigPath() {
  return path.join(app.getPath('userData'), 'desktop-config.json');
}

function readConfig() {
  try {
    const raw = fs.readFileSync(getConfigPath(), 'utf8');
    return {
      mode: null,
      liveUrl: DEFAULT_LIVE_URL,
      rememberChoice: false,
      ...JSON.parse(raw),
    };
  } catch {
    return {
      mode: null,
      liveUrl: DEFAULT_LIVE_URL,
      rememberChoice: false,
    };
  }
}

function writeConfig(patch) {
  const next = { ...readConfig(), ...patch };
  fs.mkdirSync(app.getPath('userData'), { recursive: true });
  fs.writeFileSync(getConfigPath(), JSON.stringify(next, null, 2), 'utf8');
  return next;
}

function getIconPath() {
  const ico = path.resolve(__dirname, '..', 'assets', 'icon.ico');
  const png = path.resolve(__dirname, '..', 'assets', 'icon.png');
  return fs.existsSync(ico) ? ico : png;
}

function getPngIconPath() {
  const png = path.resolve(__dirname, '..', 'assets', 'icon.png');
  return fs.existsSync(png) ? png : getIconPath();
}

function normalizeHttpUrl(raw) {
  const trimmed = String(raw || '').trim().replace(/\/+$/, '');
  if (!trimmed) return DEFAULT_LIVE_URL;
  return /^https?:\/\//i.test(trimmed) ? trimmed : `https://${trimmed}`;
}

async function checkReachable(targetUrl) {
  try {
    const url = normalizeHttpUrl(targetUrl);
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 5000);
    const res = await net.fetch(`${url}/up`, {
      method: 'GET',
      signal: controller.signal,
    }).catch(() => net.fetch(url, { method: 'HEAD', signal: controller.signal }));
    clearTimeout(timer);
    return res.status >= 200 && res.status < 500;
  } catch {
    return false;
  }
}

function updateWindowTitle() {
  if (!mainWindow || mainWindow.isDestroyed()) return;
  if (currentMode === 'offline') {
    mainWindow.setTitle('Guru Hospital — Offline Local Mode');
  } else if (currentMode === 'live') {
    mainWindow.setTitle(`Guru Hospital — Live Server (${readConfig().liveUrl})`);
  } else {
    mainWindow.setTitle('Guru Hospital — Launcher');
  }
}

function buildAppMenu() {
  const template = [
    {
      label: 'File',
      submenu: [
        {
          label: 'Open Offline Data & Backups Folder',
          click: () => shell.openPath(phpServer.resolveDataDir()),
        },
        {
          label: 'Print Current View...',
          accelerator: 'CmdOrCtrl+P',
          click: () => mainWindow?.webContents.print(),
        },
        { type: 'separator' },
        { role: 'quit', label: 'Exit Guru Hospital' },
      ],
    },
    {
      label: 'Mode',
      submenu: [
        {
          label: 'Switch to Offline Local Mode (This PC)',
          type: 'radio',
          checked: currentMode === 'offline',
          click: () => switchToOffline(false),
        },
        {
          label: `Switch to Live Server (${readConfig().liveUrl})`,
          type: 'radio',
          checked: currentMode === 'live',
          click: () => switchToLive(readConfig().liveUrl, false),
        },
        { type: 'separator' },
        {
          label: 'Open Startup Mode Chooser...',
          click: () => {
            writeConfig({ rememberChoice: false });
            openLauncher();
          },
        },
      ],
    },
    {
      label: 'Navigation',
      submenu: [
        {
          label: 'Dashboard',
          accelerator: 'Alt+Home',
          click: () => {
            const base = currentMode === 'offline' ? phpServer?.url : normalizeHttpUrl(readConfig().liveUrl);
            if (base) mainWindow?.loadURL(`${base}/dashboard`);
          },
        },
        {
          label: 'Back',
          accelerator: 'Alt+Left',
          click: () => mainWindow?.webContents.navigationHistory?.goBack(),
        },
        {
          label: 'Forward',
          accelerator: 'Alt+Right',
          click: () => mainWindow?.webContents.navigationHistory?.goForward(),
        },
        {
          label: 'Reload',
          accelerator: 'CmdOrCtrl+R',
          click: () => mainWindow?.webContents.reload(),
        },
      ],
    },
    {
      label: 'Tools',
      submenu: [
        {
          label: 'Run Offline Database Backup Now',
          enabled: currentMode === 'offline',
          click: async () => {
            try {
              const output = await phpServer.runManualBackup();
              dialog.showMessageBox(mainWindow, {
                type: 'info',
                title: 'Offline Backup Completed',
                message: 'Database backup completed successfully.',
                detail: output || 'Saved under Offline Data & Backups folder.',
              });
            } catch (err) {
              dialog.showErrorBox('Backup Failed', err?.message || String(err));
            }
          },
        },
        { type: 'separator' },
        { role: 'toggleDevTools' },
        { role: 'togglefullscreen' },
      ],
    },
    {
      label: 'Help',
      submenu: [
        {
          label: 'Offline Default Credentials',
          click: () => {
            dialog.showMessageBox(mainWindow, {
              type: 'info',
              title: 'Initial Offline Login Accounts',
              message: 'Seeded Hospital Staff Accounts (Offline Mode)',
              detail: [
                'Administrator: admin@guruhospital.local / Admin@Guru2026',
                'Doctor: dr.karthik@guruhospital.local / Doctor@Guru2026',
                'Nurse: nurse.anitha@guruhospital.local / Nurse@Guru2026',
                '',
                'Change default passwords under Administration -> Staff Users.',
              ].join('\n'),
            });
          },
        },
        {
          label: 'About Guru Hospital Desktop',
          click: () => {
            dialog.showMessageBox(mainWindow, {
              type: 'info',
              title: 'About Guru Hospital',
              message: `Guru Hospital Desktop v${app.getVersion()}`,
              detail: 'Hybrid Windows Desktop Edition\nSupports Offline SQLite Mode + Live Server Connection.\nEmbedded PHP + Playwright Chromium Document Engine.',
            });
          },
        },
      ],
    },
  ];

  Menu.setApplicationMenu(Menu.buildFromTemplate(template));
}

function sendStatus(msg) {
  if (mainWindow && !mainWindow.isDestroyed()) {
    mainWindow.webContents.send('desktop:status', msg);
  }
}

async function switchToOffline(rememberChoice = null) {
  const patch = { mode: 'offline' };
  if (rememberChoice !== null) patch.rememberChoice = Boolean(rememberChoice);
  writeConfig(patch);

  try {
    const localUrl = await phpServer.start((status) => sendStatus(status));
    currentMode = 'offline';
    updateWindowTitle();
    buildAppMenu();
    await mainWindow.loadURL(localUrl);
    return { ok: true };
  } catch (err) {
    return { ok: false, error: err?.message || 'Unable to start offline server.' };
  }
}

async function switchToLive(rawUrl, rememberChoice = null) {
  const url = normalizeHttpUrl(rawUrl);
  const patch = { mode: 'live', liveUrl: url };
  if (rememberChoice !== null) patch.rememberChoice = Boolean(rememberChoice);
  writeConfig(patch);

  phpServer.stop();
  currentMode = 'live';
  updateWindowTitle();
  buildAppMenu();

  try {
    await mainWindow.loadURL(url);
    return { ok: true };
  } catch (err) {
    return { ok: false, error: `Could not load ${url}: ${err?.message || err}` };
  }
}

function openLauncher() {
  currentMode = null;
  updateWindowTitle();
  buildAppMenu();
  mainWindow?.loadFile(path.join(__dirname, 'launcher.html'));
}

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1366,
    height: 860,
    minWidth: 1024,
    minHeight: 680,
    icon: getIconPath(),
    title: 'Guru Hospital',
    backgroundColor: '#f8fafc',
    show: !isSmokeTest,
    webPreferences: {
      preload: path.join(__dirname, 'preload.cjs'),
      contextIsolation: true,
      nodeIntegration: false,
      sandbox: true,
    },
  });

  if (!isSmokeTest) {
    mainWindow.maximize();
  }

  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    const isLocal = url.startsWith('http://127.0.0.1');
    const liveBase = normalizeHttpUrl(readConfig().liveUrl);
    if (isLocal || url.startsWith(liveBase)) {
      return {
        action: 'allow',
        overrideBrowserWindowOptions: {
          width: 1180,
          height: 820,
          icon: getIconPath(),
          autoHideMenuBar: true,
        },
      };
    }
    shell.openExternal(url);
    return { action: 'deny' };
  });

  mainWindow.webContents.session.on('will-download', (_event, item) => {
    item.setSaveDialogOptions({
      title: 'Save Document',
      defaultPath: path.join(app.getPath('downloads'), item.getFilename()),
    });
  });

  mainWindow.webContents.on('did-fail-load', (_event, errorCode, errorDescription, validatedURL) => {
    if (errorCode === -3 || !validatedURL || validatedURL.startsWith('file://')) return;
    dialog.showMessageBox(mainWindow, {
      type: 'warning',
      title: 'Connection Error',
      message: `Unable to load ${validatedURL}`,
      detail: `${errorDescription} (Code ${errorCode}).\nYou can switch to Offline Local Mode or retry.`,
      buttons: ['Switch to Offline Mode', 'Open Mode Chooser', 'Retry'],
    }).then(({ response }) => {
      if (response === 0) switchToOffline(false);
      else if (response === 1) openLauncher();
      else mainWindow?.loadURL(validatedURL);
    });
  });
}

function createTray() {
  try {
    tray = new Tray(getPngIconPath());
    tray.setToolTip('Guru Hospital');
    tray.setContextMenu(
      Menu.buildFromTemplate([
        { label: 'Show Guru Hospital', click: () => { mainWindow?.show(); mainWindow?.focus(); } },
        { label: 'Switch Mode...', click: () => { mainWindow?.show(); openLauncher(); } },
        { type: 'separator' },
        { label: 'Exit', click: () => app.quit() },
      ])
    );
    tray.on('double-click', () => {
      mainWindow?.show();
      mainWindow?.focus();
    });
  } catch {
    // Tray icon optional on some environments
  }
}

ipcMain.handle('desktop:get-state', () => ({
  version: app.getVersion(),
  ...readConfig(),
}));
ipcMain.handle('desktop:check-live', (_e, url) => checkReachable(url));
ipcMain.handle('desktop:launch-offline', (_e, opts) => switchToOffline(opts?.rememberChoice));
ipcMain.handle('desktop:launch-live', (_e, opts) => switchToLive(opts?.url, opts?.rememberChoice));
ipcMain.handle('desktop:open-data-folder', () => shell.openPath(phpServer.resolveDataDir()));

if (gotLock) {
  app.whenReady().then(async () => {
    phpServer = new OfflinePhpServer(app);
    createWindow();
    if (!isSmokeTest) {
      createTray();
      buildAppMenu();
    }

    if (isSmokeTest) {
      const smokeResultPath = path.join(app.getPath('userData'), 'smoke-result.json');
      try {
        openLauncher();
        await new Promise((resolve) => mainWindow.webContents.once('did-finish-load', resolve));
        const hasBridge = await mainWindow.webContents.executeJavaScript('Boolean(window.guruDesktop)');
        const res = await switchToOffline(false);
        if (!hasBridge || !res?.ok) {
          throw new Error(`Desktop smoke failed: hasBridge=${hasBridge}, error=${res?.error || 'unknown'}`);
        }
        const pageTitle = await mainWindow.webContents.executeJavaScript('document.title');
        const payload = { ok: true, hasBridge, pageTitle, url: phpServer.url };
        fs.writeFileSync(smokeResultPath, JSON.stringify(payload, null, 2), 'utf8');
        console.log('DESKTOP_SMOKE_TEST_OK', JSON.stringify(payload));
        phpServer?.stop();
        app.exit(0);
      } catch (err) {
        fs.writeFileSync(smokeResultPath, JSON.stringify({ ok: false, error: String(err) }, null, 2), 'utf8');
        console.error('DESKTOP_SMOKE_TEST_FAIL', err);
        phpServer?.stop();
        app.exit(1);
      }
      return;
    }

    const cfg = readConfig();
    if (cfg.rememberChoice && cfg.mode === 'offline') {
      await switchToOffline(true);
    } else if (cfg.rememberChoice && cfg.mode === 'live') {
      await switchToLive(cfg.liveUrl, true);
    } else {
      openLauncher();
    }
  });
}

app.on('will-quit', () => {
  phpServer?.stop();
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});
