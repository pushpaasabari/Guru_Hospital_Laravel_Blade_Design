import fs from 'fs';
import { launchChromium } from './launch-chromium.js';

function escapeHtml(text) {
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

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
  const browser = await launchChromium();

  try {
    const context = await browser.newContext();
    const page = await context.newPage();

    await page.setContent(htmlContent, { waitUntil: 'load' });

    const isDischargeSummary = docType === 'discharge_summary' || docType === 'discharge-summary';

    if (isDischargeSummary) {
      await page.pdf({
        path: outputFile,
        format: 'A4',
        printBackground: true,
        preferCSSPageSize: true,
        displayHeaderFooter: false,
        margin: { top: '0px', bottom: '0px', left: '0px', right: '0px' },
      });
    } else {
      await page.pdf({
        path: outputFile,
        format: 'A4',
        printBackground: true,
        preferCSSPageSize: false,
        displayHeaderFooter: true,
        headerTemplate: '<div style="font-size: 1px;"></div>',
        footerTemplate: `
          <div style="font-family: Arial, sans-serif; font-size: 8pt; width: 100%; display: flex; justify-content: space-between; padding: 0 12mm; color: #555555; box-sizing: border-box;">
            <span>${escapeHtml(headerTitle)}</span>
            <span>Page <span class="pageNumber"></span> of <span class="totalPages"></span></span>
          </div>
        `,
        margin: { top: '12mm', bottom: '15mm', left: '12mm', right: '12mm' },
      });
    }
  } finally {
    await browser.close();
  }

  console.log('PLAYWRIGHT_PDF_SUCCESS');
}

main().catch((err) => {
  console.error('Error generating PDF with Playwright:', err);
  process.exit(1);
});
