import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

async function main() {
  const args = process.argv.slice(2);
  let inputFile = null;
  let outputFile = null;
  let headerTitle = 'Guru Hospital — Confidential Medical Record';

  for (let i = 0; i < args.length; i++) {
    if (args[i] === '--input' && args[i + 1]) {
      inputFile = args[i + 1];
      i++;
    } else if (args[i] === '--output' && args[i + 1]) {
      outputFile = args[i + 1];
      i++;
    } else if (args[i] === '--title' && args[i + 1]) {
      headerTitle = args[i + 1];
      i++;
    }
  }

  if (!inputFile || !outputFile) {
    console.error('Usage: node generate-pdf.js --input <inputFile> --output <outputFile> [--title <title>]');
    process.exit(1);
  }

  const htmlContent = fs.readFileSync(inputFile, 'utf-8');

  let browser;
  // Try launching system Google Chrome, then Microsoft Edge, then default Playwright Chromium
  try {
    browser = await chromium.launch({
      channel: 'chrome',
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu'],
    });
  } catch (err) {
    try {
      browser = await chromium.launch({
        channel: 'msedge',
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
      });
    } catch {
      browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
      });
    }
  }

  const context = await browser.newContext();
  const page = await context.newPage();

  // Load content
  await page.setContent(htmlContent, { waitUntil: 'load' });

  // Render pixel-perfect medical document PDF
  await page.pdf({
    path: outputFile,
    format: 'A4',
    printBackground: true,
    preferCSSPageSize: true,
    displayHeaderFooter: true,
    headerTemplate: '<div></div>',
    footerTemplate: `
      <div style="font-family: Arial, sans-serif; font-size: 8pt; width: 100%; display: flex; justify-content: space-between; padding: 0 15mm; color: #555555; box-sizing: border-box;">
        <span>${headerTitle}</span>
        <span>Page <span class="pageNumber"></span> of <span class="totalPages"></span></span>
      </div>
    `,
    margin: {
      top: '12mm',
      bottom: '15mm',
      left: '12mm',
      right: '12mm',
    },
  });

  await browser.close();
  console.log('PLAYWRIGHT_PDF_SUCCESS');
}

main().catch((err) => {
  console.error('Error generating PDF with Playwright:', err);
  process.exit(1);
});
