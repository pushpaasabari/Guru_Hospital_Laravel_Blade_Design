import fs from 'fs';
import os from 'os';
import path from 'path';
import { chromium } from 'playwright';

const LAUNCH_ARGS = [
  '--no-sandbox',
  '--disable-setuid-sandbox',
  '--disable-dev-shm-usage',
  '--disable-gpu',
];

function getPlaywrightCacheDirs() {
  const dirs = [];
  if (process.env.PLAYWRIGHT_BROWSERS_PATH && process.env.PLAYWRIGHT_BROWSERS_PATH !== '0') {
    dirs.push(process.env.PLAYWRIGHT_BROWSERS_PATH);
  }
  if (process.platform === 'win32') {
    if (process.env.LOCALAPPDATA) dirs.push(path.join(process.env.LOCALAPPDATA, 'ms-playwright'));
    const home = process.env.USERPROFILE || os.homedir();
    if (home) dirs.push(path.join(home, 'AppData', 'Local', 'ms-playwright'));
  } else {
    const home = process.env.HOME || os.homedir();
    if (home) dirs.push(path.join(home, '.cache', 'ms-playwright'));
    dirs.push('/root/.cache/ms-playwright', '/var/www/.cache/ms-playwright');
  }
  return [...new Set(dirs.filter(Boolean))];
}

function findCachedPlaywrightExecutables() {
  const candidates = [];
  const subpaths =
    process.platform === 'win32'
      ? [
          path.join('chrome-headless-shell-win64', 'chrome-headless-shell.exe'),
          path.join('chrome-win64', 'chrome.exe'),
          path.join('chrome-win', 'chrome.exe'),
        ]
      : [
          path.join('chrome-linux', 'headless_shell'),
          path.join('chrome-headless-shell-linux64', 'chrome-headless-shell'),
          path.join('chrome-linux', 'chrome'),
          path.join('chrome-linux64', 'chrome'),
        ];

  for (const cacheDir of getPlaywrightCacheDirs()) {
    if (!fs.existsSync(cacheDir)) continue;
    let entries = [];
    try {
      entries = fs.readdirSync(cacheDir, { withFileTypes: true });
    } catch {
      continue;
    }

    const browserDirs = entries
      .filter((e) => e.isDirectory() && /^(chromium_headless_shell|chromium)-/.test(e.name))
      .map((e) => e.name)
      .sort((a, b) => {
        const revA = parseInt(a.replace(/\D+/g, ''), 10) || 0;
        const revB = parseInt(b.replace(/\D+/g, ''), 10) || 0;
        return revB - revA;
      });

    for (const dirName of browserDirs) {
      for (const rel of subpaths) {
        const full = path.join(cacheDir, dirName, rel);
        if (fs.existsSync(full)) {
          candidates.push(full);
        }
      }
    }
  }

  return candidates;
}

function getSystemBrowserPaths() {
  if (process.platform === 'win32') {
    return [
      'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
      'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
      'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
      'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
    ];
  }
  return [
    '/usr/bin/chromium',
    '/usr/bin/chromium-browser',
    '/usr/bin/google-chrome',
    '/usr/bin/google-chrome-stable',
    '/snap/bin/chromium',
  ];
}

export async function launchChromium() {
  const attempts = [];
  const errors = [];

  const envExec = process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH || process.env.CHROME_PATH;
  if (envExec) {
    attempts.push({ label: `env executable (${envExec})`, opts: { executablePath: envExec } });
  }

  try {
    const defaultExec = chromium.executablePath();
    if (defaultExec && fs.existsSync(defaultExec)) {
      attempts.push({ label: `playwright bundled (${defaultExec})`, opts: {} });
    }
  } catch {
    // Ignore if playwright metadata is unavailable
  }

  for (const cachedExec of findCachedPlaywrightExecutables()) {
    attempts.push({ label: `cached playwright (${cachedExec})`, opts: { executablePath: cachedExec } });
  }

  for (const sysExec of getSystemBrowserPaths()) {
    if (fs.existsSync(sysExec)) {
      attempts.push({ label: `system browser (${sysExec})`, opts: { executablePath: sysExec } });
    }
  }

  attempts.push({ label: 'channel chrome', opts: { channel: 'chrome' } });
  if (process.platform === 'win32') {
    attempts.push({ label: 'channel msedge', opts: { channel: 'msedge' } });
  }
  attempts.push({ label: 'default playwright chromium', opts: {} });

  for (const attempt of attempts) {
    try {
      return await chromium.launch({
        headless: true,
        args: LAUNCH_ARGS,
        ...attempt.opts,
      });
    } catch (err) {
      errors.push(`${attempt.label}: ${err?.message?.split('\n')[0] || err}`);
    }
  }

  throw new Error(
    `Unable to launch Chromium via Playwright. Install Playwright Chromium with "npx playwright install chromium". Details: ${errors.join(' | ')}`
  );
}

