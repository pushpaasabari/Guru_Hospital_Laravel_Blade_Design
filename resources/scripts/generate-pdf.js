import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

async function main() {
  const args = process.argv.slice(2);
  let inputFile = null;
  let outputFile = null;
  let headerTitle = 'GURU HOSPITAL — Confidential Medical Record';
  let docType = 'pack';

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
    } else if (args[i] === '--type' && args[i + 1]) {
      docType = args[i + 1];
      i++;
    }
  }

  if (!inputFile || !outputFile) {
    console.error('Usage: node generate-pdf.js --input <inputFile> --output <outputFile> [--title <title>] [--type <type>]');
    process.exit(1);
  }

  const htmlContent = fs.readFileSync(inputFile, 'utf-8');

  let browser;
  const launchArgs = ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu'];
  try {
    browser = await chromium.launch({
      channel: 'chrome',
      headless: true,
      args: launchArgs,
    });
  } catch (err) {
    try {
      browser = await chromium.launch({
        executablePath: 'C:\\Users\\Admin\\AppData\\Local\\ms-playwright\\chromium-1228\\chrome-win64\\chrome.exe',
        headless: true,
        args: launchArgs,
      });
    } catch {
      browser = await chromium.launch({
        headless: true,
        args: launchArgs,
      });
    }
  }

  const context = await browser.newContext();
  const page = await context.newPage();

  // Load content
  await page.setContent(htmlContent, { waitUntil: 'load' });

  const isDischargeSummary = (docType === 'discharge_summary' || docType === 'discharge-summary');

  if (isDischargeSummary) {
    // Exact 2-page A4 discharge summary layout: CSS controls @page and zero margin injection
    await page.pdf({
      path: outputFile,
      format: 'A4',
      printBackground: true,
      preferCSSPageSize: true,
      displayHeaderFooter: false,
      margin: {
        top: '0px',
        bottom: '0px',
        left: '0px',
        right: '0px',
      },
    });
  } else {
    // Render standard ISO A4 multi-document pack PDF with running header and footer
    await page.pdf({
      path: outputFile,
      format: 'A4',
      printBackground: true,
      preferCSSPageSize: false,
      displayHeaderFooter: true,
      headerTemplate: '<div style="font-size: 1px;"></div>',
      footerTemplate: `
        <div style="font-family: Arial, sans-serif; font-size: 8pt; width: 100%; display: flex; justify-content: space-between; padding: 0 12mm; color: #555555; box-sizing: border-box;">
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
  }

  await browser.close();
  console.log('PLAYWRIGHT_PDF_SUCCESS');
}

main().catch((err) => {
  console.error('Error generating PDF with Playwright:', err);
  process.exit(1);
});

