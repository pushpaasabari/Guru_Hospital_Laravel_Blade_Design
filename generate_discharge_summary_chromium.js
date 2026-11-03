import React from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import esbuild from 'esbuild';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Compile JSX template on the fly using bundled esbuild
const jsxPath = path.join(__dirname, 'resources/js/Components/DischargeSummaryPdfTemplate.jsx');
const jsPath = path.join(__dirname, 'resources/js/Components/DischargeSummaryPdfTemplate.js');
esbuild.buildSync({
    entryPoints: [jsxPath],
    outfile: jsPath,
    format: 'esm'
});

const { default: DischargeSummaryPdfTemplate } = await import('./resources/js/Components/DischargeSummaryPdfTemplate.js');

const ANITHA_DATA = {
    patient_name: 'Mrs. Anitha',
    age_sex: '23 / Female',
    ip_no: '2026/567',
    uhid: '2023/3931',
    phone: '7373667465',
    admission_at: '26.08.2026 at 02.45 AM',
    discharge_at: '29.08.2026 at 04.00 PM',
    address: 'W/O Sarath, Veerasanur',
    picme_no: '',
    consultant: 'Dr. Ambika MS OG',
    department: 'Obstetrics & Gynaecology',
    procedure_at: '26.08.2026 at 03:18 AM',
    surgeon: 'Dr. Ambika MS OG',
    anaesthetist: 'Dr. M.S Karthick (SA)',
    assisted_by: 'Staff Nurse Anitha',
    diagnosis: 'G3P1L1A1 / TWINS / Preterm / PROM',
    surgery_performed: 'Twin A: Labour natural with episiotomy | Twin B: Emergency LSCS',
    presenting_complaints: 'G3P1L1A1 / TWINS / EDD 22-9-2026 / FH Good / admitted for safe confinement\nC/O abdominal Pain',
    clinical_examination: 'O/E – A febrile, No Pallor, No Pedal edema / CVS & RS – NAD\nP/A: Uterus Overdistended, Not Acting, Both FH good',
    procedure_note_p1: 'Labour natural with episiotomy with good uterine contraction, full cervix dilated. After putting episiotomy, Patient Delivered a alive Twin A male baby By Breech, Baby Handed over to pediatrician. Twin B baby not progressed Delivered Hence Planned To proceed C-Section.',
    procedure_note_p2: 'Under SA, Abdomen opened in layers by SPT incision. LSCS done to deliver a alive Twin B male baby, cried soon after birth. Baby Handed over to pediatrician AMTSL followed. Uterine wound closed in 2 layers. After securing perfect Hemostasis, Abdomen closed in layer. AMTSL Followed. Uterus firm & contracted well. Episiotomy wound closed in layers. No Undue Bleeding P/V.',
    babies: [
        {
            title: 'Newborn #1 - Twin A',
            sex: 'Alive male baby',
            born: '26.08.2026 at 03:18 AM',
            mode: 'Labour natural breech with episiotomy',
            weight: '2.250 kg',
            apgar: '7/10, 8/10',
            neonatal_care: 'RDS (+). Handed over to pediatrician; shifted to NICU.'
        },
        {
            title: 'Newborn #2 - Twin B',
            sex: 'Alive male baby',
            born: '26.08.2026 at 03:44 AM',
            mode: 'Emergency LSCS under SA',
            weight: '2.250 kg',
            apgar: '8/10, 9/10',
            neonatal_care: 'Cried soon after birth. Baby handed over to pediatrician. AMTSL followed.'
        }
    ],
    treatment_given: [
        { medicine: 'IV Fluids', dose: '—' },
        { medicine: 'Inj. Pipzo', dose: '4.5 g BD IV' },
        { medicine: 'Inj. Metrogyl', dose: '100 mg BD' },
        { medicine: 'Inj. Pan', dose: '40 IV BD' }
    ],
    investigations: [
        { label: 'HB', value: '12.9 mgs %' },
        { label: 'BLOOD GROUP', value: 'O positive' },
        { label: 'HIV', value: 'Negative' },
        { label: 'HCV', value: 'Negative' },
        { label: 'VDRL', value: 'Negative' },
        { label: 'HBSAG', value: 'Negative' },
        { label: 'UREA', value: '9.7 g/dl' },
        { label: 'CREATININE', value: '0.86 mg/dl' },
        { label: 'URIC ACID', value: '4.3 mg/dl' },
        { label: 'RBS', value: '100.8 mg/dl' }
    ],
    condition_on_discharge: {
        general: 'Pt’s general condition good',
        bp: '110/70 mmHg',
        pulse: '86 b/min',
        pa: 'Uterus contracted well; no undue bleeding pv'
    },
    medications: [
        { medicine: 'Tab. Cefixime + Sulbactam', frequency: '1-0-1', duration: '7 days', timing: 'After food' },
        { medicine: 'Tab. Acendol-P', frequency: '1-0-1', duration: '7 days', timing: 'After food' },
        { medicine: 'Cap. Cyra-D', frequency: '1-0-1', duration: '7 days', timing: 'Before food' }
    ],
    advice_list: [
        'Immunisation',
        'Exclusive breast feeding for 6 months',
        'Post Natal Exercise'
    ],
    review_date: '03/09/2026',
    emergency_contacts: '04342-262266, +91-7418084266'
};

const LONG_ADDRESS = 'W/O Sarath, Door No. 4/122-B, Mariamman Kovil Street, Near Primary Health Centre & Panchayat Union Middle School, Veerasanur Village & Post, Dharmapuri District, Tamil Nadu - 636809, Landmark: Near Overhead Water Tank';

export async function generateDischargeSummaryPdf(outputPath = 'Guru_Hospital_Discharge_Summary_Anitha_IP2026-567.pdf', options = {}) {
    const isLongAddress = options.longAddress || process.argv.includes('--long-address');
    const data = {
        ...ANITHA_DATA,
        address: isLongAddress ? LONG_ADDRESS : ANITHA_DATA.address
    };

    console.log(`[React Chromium] Rendering Discharge Summary with React SSR (Long Address: ${isLongAddress ? 'YES' : 'NO'})...`);

    // Render static HTML from React component
    const htmlBody = renderToStaticMarkup(React.createElement(DischargeSummaryPdfTemplate, { data }));
    const fullHtml = `<!DOCTYPE html>\n${htmlBody}`;

    const tempHtmlPath = path.join(__dirname, '.temp_react_print.html');
    fs.writeFileSync(tempHtmlPath, fullHtml, 'utf-8');

    // Launch Chromium via Playwright
    let browser;
    const launchArgs = ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu'];
    try {
        browser = await chromium.launch({
            channel: 'chrome',
            headless: true,
            args: launchArgs
        });
    } catch {
        browser = await chromium.launch({
            executablePath: 'C:\\Users\\Admin\\AppData\\Local\\ms-playwright\\chromium-1228\\chrome-win64\\chrome.exe',
            headless: true,
            args: launchArgs
        });
    }

    const context = await browser.newContext();
    const page = await context.newPage();

    await page.setContent(fullHtml, { waitUntil: 'load' });

    console.log(`[React Chromium] Printing to PDF via Chromium Blink engine: ${outputPath}`);
    await page.pdf({
        path: outputPath,
        format: 'A4',
        printBackground: true,
        preferCSSPageSize: true, // Honors exact @page size and CSS sheets
        displayHeaderFooter: false, // Page 1 has zero header/footer; Page 2 has running header/footer in HTML
        margin: {
            top: '0px',
            bottom: '0px',
            left: '0px',
            right: '0px'
        }
    });

    await browser.close();
    try { fs.unlinkSync(tempHtmlPath); } catch {}

    console.log(`[React Chromium] Successfully generated: ${outputPath}`);
    return outputPath;
}

if (process.argv[1] && process.argv[1].endsWith('generate_discharge_summary_chromium.js')) {
    const isLong = process.argv.includes('--long-address');
    const outName = isLong ? 'Guru_Hospital_Discharge_Summary_Anitha_LongAddress.pdf' : 'Guru_Hospital_Discharge_Summary_Anitha_IP2026-567.pdf';
    generateDischargeSummaryPdf(outName, { longAddress: isLong })
        .then(() => process.exit(0))
        .catch(err => {
            console.error('Error generating PDF:', err);
            process.exit(1);
        });
}
