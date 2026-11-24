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

    const isDischargeSummary =
      docType === 'discharge_summary' ||
      docType === 'discharge-summary' ||
      docType === 'discharge_summary_a4' ||
      docType === 'discharge-summary-a4';

    if (isDischargeSummary) {
      await page.evaluate(() => {
        const sheets = document.querySelectorAll('.a4-page-content');
        if (sheets.length === 2) {
          const p1 = sheets[0];
          const p2 = sheets[1];
          const p1Sheet = p1.closest('.a4-page');
          const overflowTarget = p2.querySelector('[data-overflow-target="page2"]');

          // Pass 1: Optimize Page 1 spacing before shrinking fonts
          if (p1.scrollHeight > p1.clientHeight + 1) {
            p1Sheet?.classList.add('a4-density-spacing');
          }

          // Pass 2: Move overflow blocks from Page 1 (204mm printable) onto Page 2 (280mm full sheet)
          if (p1.scrollHeight > p1.clientHeight + 1 && overflowTarget) {
            const movables = Array.from(p1.querySelectorAll('[data-overflow-movable="page1"]')).reverse();
            for (const block of movables) {
              if (p1.scrollHeight <= p1.clientHeight + 1) break;

              // Enforce clinical rule: INVESTIGATIONS must always appear before SURGERY / PROCEDURES.
              // Never move Investigations to Page 2 if Surgery / Procedures is still remaining on Page 1.
              const isInv = block.textContent.includes('Investigations');
              const surgeryStillOnP1 = Array.from(p1.querySelectorAll('[data-overflow-movable="page1"]')).some(
                el => el !== block && el.textContent.includes('Surgery / Procedures')
              );
              if (isInv && surgeryStillOnP1) {
                continue;
              }

              const note = document.createElement('div');
              note.className = 'a4-continued-note';
              note.textContent = 'Continued from Page 1';
              overflowTarget.insertBefore(block, overflowTarget.firstChild);
              overflowTarget.insertBefore(note, block);
            }
          }
        }

        // Pass 3: Apply graduated density/compact styles, then auto-fit cleanly keeping footer anchored to bottom
        for (const contentEl of sheets) {
          const sheetEl = contentEl.closest('.a4-page');
          for (const cls of ['a4-density-spacing', 'a4-compact-1', 'a4-compact-2']) {
            if (contentEl.scrollHeight <= contentEl.clientHeight + 1) break;
            sheetEl?.classList.add(cls);
          }

          const targetHeight = contentEl.clientHeight;
          if (contentEl.scrollHeight > targetHeight + 1) {
            let ratio = Math.max(0.82, targetHeight / contentEl.scrollHeight);
            contentEl.style.transformOrigin = 'top left';
            contentEl.style.width = `${(100 / ratio).toFixed(3)}%`;
            contentEl.style.height = 'auto';
            contentEl.style.maxHeight = 'none';

            // Recompute required ratio after horizontal expansion reduces wrapping
            const reflowedHeight = contentEl.scrollHeight;
            if (reflowedHeight * ratio > targetHeight + 1) {
              ratio = Math.max(0.78, targetHeight / reflowedHeight);
              contentEl.style.width = `${(100 / ratio).toFixed(3)}%`;
            }

            contentEl.style.minHeight = `${Math.floor(targetHeight / ratio)}px`;
            contentEl.style.transform = `scale(${ratio.toFixed(3)})`;
          }
        }
      });

      await page.pdf({
        path: outputFile,
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
