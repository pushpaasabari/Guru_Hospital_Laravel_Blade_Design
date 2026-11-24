<?php

namespace App\Services;

use App\Models\Admission;
use Carbon\Carbon;

class DischargeSummaryFormatter
{
    /**
     * Standard order for core lab investigations and full hospital catalog.
     */
    public const INVESTIGATION_ORDER = [
        'HB' => 1,
        'BLOOD GROUP' => 2,
        'BP' => 3,
        'PULSE' => 4,
        'SPO2' => 5,
        'HIV' => 6,
        'HCV' => 7,
        'VDRL' => 8,
        'HBSAG' => 9,
        'UREA' => 10,
        'CREATININE' => 11,
        'URIC ACID' => 12,
        'RBS' => 13,
        'ECG' => 14,
        'CBC' => 12,
        'ESR' => 13,
        'BT, CT' => 14,
        'PT - INR' => 15,
        'APTT' => 16,
        'PERIPHERAL SMEAR' => 17,
        'URINE ROUTINE' => 18,
        'RFT' => 19,
        'LFT' => 20,
        'SR. BILIRUBIN' => 21,
        'SERUM ELECTROLYTES' => 22,
        'FASTING (FBS)' => 23,
        'PPBS' => 24,
        'HBA1C' => 25,
        'OGCT' => 26,
        'SERUM CALCIUM' => 27,
        'SERUM AMYLASE' => 28,
        'SERUM LIPASE' => 29,
        'TOTAL IRON' => 30,
        'TIBC' => 31,
        'SERUM FERRITIN' => 32,
        'CHOLESTEROL' => 33,
        'TRIGLYCERIDES' => 34,
        'HDL' => 35,
        'LDL' => 36,
        'VLDL' => 37,
        'CRP' => 38,
        'WIDAL' => 39,
        'RA FACTOR' => 40,
        'ASO TITER' => 41,
        'DENGUE SEROLOGY' => 42,
        'SCRUB TYPHUS' => 43,
        'TSH' => 44,
        'TFT' => 45,
        'TROPONIN-T' => 46,
        'CK-MB' => 47,
        'CHEST X-RAY (PA)' => 48,
        'X-RAY KUB' => 49,
        'URINE C/S' => 50,
        'SPUTUM C/S' => 51,
        'PUS C/S' => 52,
        'BLOOD C/S' => 53,
        'SPUTUM AFB' => 54,
        'GEN-XPERT (MTB)' => 55,
        'CA 125' => 56,
        'FPSA' => 57,
        'S PSA' => 58,
        'TESTOSTERONE' => 59,
        'ESTRADIOL' => 60,
        'AMH' => 61,
        'ANA' => 62,
        'PROLACTIN' => 63,
        'LH, FSH' => 64,
        'D-DIMER' => 65,
        'BETA HCG' => 66,
        'SEMEN ANALYSIS' => 67,
        'SEMEN C/S' => 68,
        'SEMEN-XPERT' => 69,
        'SPOT PCR' => 70,
        'SPOT MICROALBUMIN' => 71,
        'SPOT SODIUM' => 72,
        'SMEAR FOR MP/MF' => 73,
        'PACKAGE - 1' => 74,
        'PACKAGE - 2' => 75,
        'PACKAGE - 3' => 76,
    ];

    /**
     * Standard clinical units and normal reference ranges for laboratory investigations.
     * Comprehensive catalog derived directly from Guru Hospital's official Laboratory Requisition Slip.
     */
    public const INVESTIGATION_STANDARDS = [
        'HB' => [
            'unit' => 'g/dL',
            'normal_range' => '12.0 - 16.0',
            'reference' => '12.0 - 16.0 g/dL',
        ],
        'BLOOD GROUP' => [
            'unit' => '',
            'normal_range' => 'Compatible',
            'reference' => 'ABO/Rh Compatible',
        ],
        'BP' => [
            'unit' => 'mmHg',
            'normal_range' => '120/80',
            'reference' => '120/80 mmHg',
        ],
        'PULSE' => [
            'unit' => 'bpm',
            'normal_range' => '60 - 100',
            'reference' => '60 - 100 bpm',
        ],
        'SPO2' => [
            'unit' => '%',
            'normal_range' => '95 - 100',
            'reference' => '95 - 100 %',
        ],
        'HIV' => [
            'unit' => '',
            'normal_range' => 'Non-Reactive',
            'reference' => 'Non-Reactive',
        ],
        'HCV' => [
            'unit' => '',
            'normal_range' => 'Non-Reactive',
            'reference' => 'Non-Reactive',
        ],
        'VDRL' => [
            'unit' => '',
            'normal_range' => 'Non-Reactive',
            'reference' => 'Non-Reactive',
        ],
        'HBSAG' => [
            'unit' => '',
            'normal_range' => 'Non-Reactive',
            'reference' => 'Non-Reactive',
        ],
        'UREA' => [
            'unit' => 'mg/dL',
            'normal_range' => '15 - 40',
            'reference' => '15 - 40 mg/dL',
        ],
        'CREATININE' => [
            'unit' => 'mg/dL',
            'normal_range' => '0.6 - 1.2',
            'reference' => '0.6 - 1.2 mg/dL',
        ],
        'URIC ACID' => [
            'unit' => 'mg/dL',
            'normal_range' => '3.5 - 7.2',
            'reference' => '3.5 - 7.2 mg/dL',
        ],
        'RBS' => [
            'unit' => 'mg/dL',
            'normal_range' => '70 - 140',
            'reference' => '70 - 140 mg/dL',
        ],
        'ECG' => [
            'unit' => '',
            'normal_range' => 'Normal Sinus',
            'reference' => 'Normal Sinus Rhythm',
        ],
        'CBC' => [
            'unit' => 'cells/cu.mm',
            'normal_range' => 'Normal Hemogram',
            'reference' => 'TC: 4000-11000 | Plt: 1.5-4.5L',
        ],
        'ESR' => [
            'unit' => 'mm/hr',
            'normal_range' => '0 - 20',
            'reference' => '0 - 20 mm/hr',
        ],
        'BT, CT' => [
            'unit' => 'min',
            'normal_range' => 'BT: 2-6 | CT: 4-10',
            'reference' => 'BT: 2-6 min | CT: 4-10 min',
        ],
        'SMEAR FOR MP/MF' => [
            'unit' => '',
            'normal_range' => 'Negative',
            'reference' => 'Negative for MP & MF',
        ],
        'PT - INR' => [
            'unit' => 'INR',
            'normal_range' => '0.8 - 1.2',
            'reference' => '0.8 - 1.2 INR',
        ],
        'APTT' => [
            'unit' => 'sec',
            'normal_range' => '25 - 35',
            'reference' => '25 - 35 sec',
        ],
        'PERIPHERAL SMEAR' => [
            'unit' => '',
            'normal_range' => 'Normocytic Normochromic',
            'reference' => 'Normocytic Normochromic',
        ],
        'TOTAL IRON' => [
            'unit' => 'mcg/dL',
            'normal_range' => '60 - 170',
            'reference' => '60 - 170 mcg/dL',
        ],
        'TIBC' => [
            'unit' => 'mcg/dL',
            'normal_range' => '240 - 450',
            'reference' => '240 - 450 mcg/dL',
        ],
        'SERUM FERRITIN' => [
            'unit' => 'ng/mL',
            'normal_range' => '15 - 200',
            'reference' => '15 - 200 ng/mL',
        ],
        'CHEST X-RAY (PA)' => [
            'unit' => '',
            'normal_range' => 'Normal Chest',
            'reference' => 'Clear Bilateral Lung Fields',
        ],
        'X-RAY KUB' => [
            'unit' => '',
            'normal_range' => 'No Calculus',
            'reference' => 'No Radio-opaque Calculi',
        ],
        'TROPONIN-T' => [
            'unit' => 'ng/mL',
            'normal_range' => '< 0.01',
            'reference' => '< 0.01 ng/mL (Negative)',
        ],
        'CK-MB' => [
            'unit' => 'U/L',
            'normal_range' => '< 25',
            'reference' => '0 - 25 U/L',
        ],
        'SPUTUM AFB' => [
            'unit' => '',
            'normal_range' => 'Negative',
            'reference' => 'Negative for AFB',
        ],
        'GEN-XPERT (MTB)' => [
            'unit' => '',
            'normal_range' => 'Not Detected',
            'reference' => 'MTB Not Detected',
        ],
        'SEMEN ANALYSIS' => [
            'unit' => 'mil/mL',
            'normal_range' => '> 15',
            'reference' => 'Count > 15 mil/mL | Motility > 40%',
        ],
        'SEMEN C/S' => [
            'unit' => '',
            'normal_range' => 'Sterile',
            'reference' => 'No Pathogen Grown',
        ],
        'SEMEN-XPERT' => [
            'unit' => '',
            'normal_range' => 'Not Detected',
            'reference' => 'MTB Not Detected',
        ],
        'OGCT' => [
            'unit' => 'mg/dL',
            'normal_range' => '< 140',
            'reference' => '< 140 mg/dL',
        ],
        'FASTING (FBS)' => [
            'unit' => 'mg/dL',
            'normal_range' => '70 - 100',
            'reference' => '70 - 100 mg/dL',
        ],
        'PPBS' => [
            'unit' => 'mg/dL',
            'normal_range' => '< 140',
            'reference' => '< 140 mg/dL',
        ],
        'HBA1C' => [
            'unit' => '%',
            'normal_range' => '< 5.7',
            'reference' => '< 5.7 %',
        ],
        'RFT' => [
            'unit' => 'mg/dL',
            'normal_range' => 'Normal RFT',
            'reference' => 'Urea: 15-40 | Creat: 0.6-1.2',
        ],
        'LFT' => [
            'unit' => '',
            'normal_range' => 'Normal LFT',
            'reference' => 'Normal Hepatic Profile',
        ],
        'SR. BILIRUBIN' => [
            'unit' => 'mg/dL',
            'normal_range' => '0.2 - 1.2',
            'reference' => '0.2 - 1.2 mg/dL',
        ],
        'SERUM ELECTROLYTES' => [
            'unit' => 'mmol/L',
            'normal_range' => 'Na: 135-145 | K: 3.5-5.0',
            'reference' => 'Na: 135-145 | K: 3.5-5.0 | Cl: 98-107',
        ],
        'SERUM CALCIUM' => [
            'unit' => 'mg/dL',
            'normal_range' => '8.5 - 10.5',
            'reference' => '8.5 - 10.5 mg/dL',
        ],
        'SERUM AMYLASE' => [
            'unit' => 'U/L',
            'normal_range' => '28 - 100',
            'reference' => '28 - 100 U/L',
        ],
        'SERUM LIPASE' => [
            'unit' => 'U/L',
            'normal_range' => '10 - 140',
            'reference' => '10 - 140 U/L',
        ],
        'URINE ROUTINE' => [
            'unit' => '',
            'normal_range' => 'Alb: Nil | Sug: Nil | Pus: 1-2',
            'reference' => 'Albumin: Nil | Sugar: Nil | Pus: 1-2',
        ],
        'SPOT PCR' => [
            'unit' => 'mg/g',
            'normal_range' => '< 150',
            'reference' => '< 150 mg/g',
        ],
        'SPOT MICROALBUMIN' => [
            'unit' => 'mg/L',
            'normal_range' => '< 20',
            'reference' => '< 20 mg/L',
        ],
        'SPOT SODIUM' => [
            'unit' => 'mmol/L',
            'normal_range' => '40 - 220',
            'reference' => '40 - 220 mmol/L',
        ],
        'CHOLESTEROL' => [
            'unit' => 'mg/dL',
            'normal_range' => '< 200',
            'reference' => '< 200 mg/dL',
        ],
        'TRIGLYCERIDES' => [
            'unit' => 'mg/dL',
            'normal_range' => '< 150',
            'reference' => '< 150 mg/dL',
        ],
        'HDL' => [
            'unit' => 'mg/dL',
            'normal_range' => '> 40',
            'reference' => '> 40 mg/dL',
        ],
        'LDL' => [
            'unit' => 'mg/dL',
            'normal_range' => '< 100',
            'reference' => '< 100 mg/dL',
        ],
        'VLDL' => [
            'unit' => 'mg/dL',
            'normal_range' => '2 - 30',
            'reference' => '2 - 30 mg/dL',
        ],
        'CA 125' => [
            'unit' => 'U/mL',
            'normal_range' => '< 35',
            'reference' => '< 35 U/mL',
        ],
        'FPSA' => [
            'unit' => 'ng/mL',
            'normal_range' => '> 0.25',
            'reference' => 'Ratio > 0.25',
        ],
        'S PSA' => [
            'unit' => 'ng/mL',
            'normal_range' => '< 4.0',
            'reference' => '< 4.0 ng/mL',
        ],
        'CRP' => [
            'unit' => 'mg/L',
            'normal_range' => '< 6.0',
            'reference' => '< 6.0 mg/L',
        ],
        'WIDAL' => [
            'unit' => '',
            'normal_range' => 'Negative',
            'reference' => 'Titers < 1:80',
        ],
        'RA FACTOR' => [
            'unit' => 'IU/mL',
            'normal_range' => '< 14',
            'reference' => '< 14 IU/mL',
        ],
        'ASO TITER' => [
            'unit' => 'IU/mL',
            'normal_range' => '< 200',
            'reference' => '< 200 IU/mL',
        ],
        'DENGUE SEROLOGY' => [
            'unit' => '',
            'normal_range' => 'Negative',
            'reference' => 'NS1, IgM, IgG: Negative',
        ],
        'SCRUB TYPHUS' => [
            'unit' => '',
            'normal_range' => 'Negative',
            'reference' => 'Negative',
        ],
        'URINE C/S' => [
            'unit' => '',
            'normal_range' => 'Sterile',
            'reference' => 'No Growth After 48 hrs',
        ],
        'SPUTUM C/S' => [
            'unit' => '',
            'normal_range' => 'Normal Flora',
            'reference' => 'Normal Commensals Grown',
        ],
        'PUS C/S' => [
            'unit' => '',
            'normal_range' => 'Sterile',
            'reference' => 'No Growth After 48 hrs',
        ],
        'BLOOD C/S' => [
            'unit' => '',
            'normal_range' => 'Sterile',
            'reference' => 'Sterile After 5 Days',
        ],
        'TSH' => [
            'unit' => 'mIU/L',
            'normal_range' => '0.4 - 4.2',
            'reference' => '0.4 - 4.2 mIU/L',
        ],
        'TFT' => [
            'unit' => '',
            'normal_range' => 'Euthyroid',
            'reference' => 'T3, T4, TSH WNL',
        ],
        'TESTOSTERONE' => [
            'unit' => 'ng/dL',
            'normal_range' => '280 - 1100 (M)',
            'reference' => 'Male: 280-1100 | Fem: 15-70',
        ],
        'ESTRADIOL' => [
            'unit' => 'pg/mL',
            'normal_range' => '30 - 400',
            'reference' => '30 - 400 pg/mL',
        ],
        'AMH' => [
            'unit' => 'ng/mL',
            'normal_range' => '1.5 - 4.0',
            'reference' => '1.5 - 4.0 ng/mL',
        ],
        'ANA' => [
            'unit' => '',
            'normal_range' => 'Negative',
            'reference' => 'Negative (< 1:80)',
        ],
        'PROLACTIN' => [
            'unit' => 'ng/mL',
            'normal_range' => '4.8 - 23.3',
            'reference' => '4.8 - 23.3 ng/mL',
        ],
        'LH, FSH' => [
            'unit' => 'mIU/mL',
            'normal_range' => 'LH: 2-15 | FSH: 3-12',
            'reference' => 'LH: 2-15 | FSH: 3-12 mIU/mL',
        ],
        'D-DIMER' => [
            'unit' => 'mcg/mL FEU',
            'normal_range' => '< 0.5',
            'reference' => '< 0.5 mcg/mL FEU',
        ],
        'BETA HCG' => [
            'unit' => 'mIU/mL',
            'normal_range' => '< 5.0 (Non-Preg)',
            'reference' => '< 5.0 mIU/mL',
        ],
        'PACKAGE - 1' => [
            'unit' => '',
            'normal_range' => 'Pre-op Clearance',
            'reference' => 'Pre-Operative Profile Normal',
        ],
        'PACKAGE - 2' => [
            'unit' => '',
            'normal_range' => 'Normal Profile',
            'reference' => 'Master Health Profile Normal',
        ],
        'PACKAGE - 3' => [
            'unit' => '',
            'normal_range' => 'Normal Profile',
            'reference' => 'Executive Cardiac Profile Normal',
        ],
    ];

    /**
     * Parse treatment course into structured medication rows with dose & route.
     *
     * @param string|null $text
     * @return array<int, array{medicine: string, dose: string}>
     */
    public static function formatTreatmentCourse(?string $text): array
    {
        if (!$text) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*•-");
            if (!$line) {
                continue;
            }

            if (preg_match('/^Treated with\s*(.*)$/i', $line, $m)) {
                $rows[] = [
                    'medicine' => trim($m[1]) ?: 'IV Fluids',
                    'dose' => '—',
                ];
                continue;
            }

            $normalized = preg_replace('/^(Inj|Tab|Cap|Syp)-/i', '$1. ', $line);

            $isProseSentence = str_word_count($normalized) > 8 && !preg_match('/^(Inj|Tab|Cap|Syp)\./i', $normalized);

            if (!$isProseSentence && preg_match('/^(Inj\.\s+[A-Za-z0-9_\-+]+|Tab\.\s+[A-Za-z0-9_\-+\s]+|Cap\.\s+[A-Za-z0-9_\-+\s]+|Syp\.\s+[A-Za-z0-9_\-+\s]+|[A-Za-z0-9_\-+]+)\s+(.+)$/i', $normalized, $m)) {
                $med = trim($m[1]);
                $dose = trim($m[2]);

                $dose = preg_replace('/(\d+)\s*(mg|g|mcg|ml)/i', '$1 $2', $dose);
                $dose = preg_replace_callback('/\b(iv|im|sc|po|od|bd|tds|qid|stat|prn)\b/i', fn($match) => strtoupper($match[0]), $dose);
                $rows[] = [
                    'medicine' => $med,
                    'dose' => $dose,
                ];
            } else {
                $rows[] = [
                    'medicine' => $normalized,
                    'dose' => '—',
                ];
            }
        }

        return $rows;
    }

    /**
     * Format immediate postoperative orders into structured rows (Order #, Item/Instruction, Route/Timing).
     *
     * @param string|null $text
     * @return array<int, array{sno: int, order: string, instruction: string}>
     */
    public static function formatPostoperativeOrders(?string $text): array
    {
        if (!$text) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        $rows = [];
        $index = 1;

        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*•-");
            if (!$line) {
                continue;
            }

            // Remove leading numbering like "1. ", "1) ", "1 - ", "#1 "
            $cleaned = trim((string) preg_replace('/^(?:#?\d+[\.\)\-:]|\([0-9]+\))\s*/', '', $line));
            if ($cleaned === '') {
                continue;
            }

            // Check if pipe or tab separated: "Order / Medicine | Route / Timing"
            if (str_contains($cleaned, '|')) {
                $parts = array_map('trim', explode('|', $cleaned, 2));
                $rows[] = [
                    'sno' => $index++,
                    'order' => $parts[0] ?: '—',
                    'instruction' => $parts[1] ?? '',
                ];
            } elseif (preg_match('/^(Inj\.\s+[A-Za-z0-9_\-+]+|Tab\.\s+[A-Za-z0-9_\-+\s]+|Cap\.\s+[A-Za-z0-9_\-+\s]+|Syp\.\s+[A-Za-z0-9_\-+\s]+|IV\s+Fluids?|RL|DNS|NS)\s+(.+)$/i', $cleaned, $m)) {
                $rows[] = [
                    'sno' => $index++,
                    'order' => trim($m[1]),
                    'instruction' => trim($m[2]),
                ];
            } else {
                $rows[] = [
                    'sno' => $index++,
                    'order' => $cleaned,
                    'instruction' => '',
                ];
            }
        }

        return $rows;
    }

    /**
     * Normalize an investigation label to match the standard catalog.
     */
    public static function normalizeInvestigationKey(string $rawLabel): string
    {
        $key = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $rawLabel));
        return match ($key) {
            'hb', 'haemoglobin' => 'HB',
            'bg', 'bloodgroup', 'bloodgrouping', 'rh' => 'BLOOD GROUP',
            'bp', 'bloodpressure' => 'BP',
            'pulse', 'pulserate', 'hr', 'heartrate' => 'PULSE',
            'spo2', 'oxygen', 'sao2', 'oxygensaturation' => 'SPO2',
            'hiv' => 'HIV',
            'hcv' => 'HCV',
            'vdrl' => 'VDRL',
            'hbsag', 'hbs' => 'HBSAG',
            'urea', 'bloodurea' => 'UREA',
            'creatinine', 'creatine', 'serumcreatinine' => 'CREATININE',
            'uricacid', 'serumuricacid' => 'URIC ACID',
            'rbs', 'bloodsugar', 'randombloodsugar' => 'RBS',
            'ecg', 'ekg', 'electrocardiogram' => 'ECG',
            'cbc', 'completebloodcount', 'hemogram' => 'CBC',
            'esr', 'erythrocytesedimentationrate' => 'ESR',
            'btct', 'bt', 'ct', 'bleedingtimeclottingtime' => 'BT, CT',
            'smearformpmf', 'smearformp', 'mpmf', 'smearmpmf' => 'SMEAR FOR MP/MF',
            'ptinr', 'pt', 'inr', 'prothrombintime' => 'PT - INR',
            'aptt' => 'APTT',
            'peripheralsmear', 'ps', 'peripheralsmearstudy' => 'PERIPHERAL SMEAR',
            'totaliron', 'serumiron', 'iron' => 'TOTAL IRON',
            'tibc' => 'TIBC',
            'ferritin', 'serumferritin' => 'SERUM FERRITIN',
            'xraychest', 'chestxray', 'chestpa', 'xraychestpa', 'xraychestpaview', 'chestpaview', 'chest' => 'CHEST X-RAY (PA)',
            'xraykub', 'kub' => 'X-RAY KUB',
            'troponint', 'tropt', 'trop' => 'TROPONIN-T',
            'ckmb' => 'CK-MB',
            'sputumafb', 'afb' => 'SPUTUM AFB',
            'genxpert', 'genxpertmtbrif', 'genxpertmtb', 'xpert' => 'GEN-XPERT (MTB)',
            'semenanalysis', 'semencount' => 'SEMEN ANALYSIS',
            'semencs' => 'SEMEN C/S',
            'semenxpert' => 'SEMEN-XPERT',
            'ogct' => 'OGCT',
            'fasting', 'fbs', 'fastingbloodsugar' => 'FASTING (FBS)',
            'postprandial', 'ppbs', 'postprandialbloodsugar' => 'PPBS',
            'hba1c', 'glycatedhemoglobin' => 'HBA1C',
            'rft', 'renalfunctiontest', 'renalfunction' => 'RFT',
            'lft', 'liverfunctiontest', 'liverfunction' => 'LFT',
            'srbilirubin', 'bilirubin', 'totalbilirubin', 'serumbilirubin' => 'SR. BILIRUBIN',
            'electrolytes', 'serumelectrolytes', 'electrolyte' => 'SERUM ELECTROLYTES',
            'calcium', 'serumcalcium' => 'SERUM CALCIUM',
            'amylase', 'serumamylase' => 'SERUM AMYLASE',
            'lipase', 'serumlipase' => 'SERUM LIPASE',
            'urineroutine', 'urinalysis', 'urineanalysis', 'urinecomplete', 'urine' => 'URINE ROUTINE',
            'spotpcr' => 'SPOT PCR',
            'spotmicroalbumin' => 'SPOT MICROALBUMIN',
            'spotsodium' => 'SPOT SODIUM',
            'cholestrol', 'cholesterol', 'totalcholesterol' => 'CHOLESTEROL',
            'triglycerides', 'tg', 'tgl' => 'TRIGLYCERIDES',
            'hdl', 'hdlcholesterol' => 'HDL',
            'ldl', 'ldlcholesterol' => 'LDL',
            'vldl', 'vldlcholesterol' => 'VLDL',
            'ca125' => 'CA 125',
            'fpsa', 'freepsa' => 'FPSA',
            'spsa', 'psa', 'serumpsa' => 'S PSA',
            'crp', 'creactiveprotein' => 'CRP',
            'widal', 'widaltest' => 'WIDAL',
            'rafactor', 'rheumatoidfactor' => 'RA FACTOR',
            'asotiter', 'aso' => 'ASO TITER',
            'dengue', 'dengueserology', 'denguens1', 'denguepanel' => 'DENGUE SEROLOGY',
            'scrubtyphus' => 'SCRUB TYPHUS',
            'urinecs' => 'URINE C/S',
            'sputumcs' => 'SPUTUM C/S',
            'puscs' => 'PUS C/S',
            'bloodcs' => 'BLOOD C/S',
            'tsh', 'thyroidstimulatinghormone' => 'TSH',
            'tft', 'thyroidfunctiontest' => 'TFT',
            'testosterone', 'serumtestosterone' => 'TESTOSTERONE',
            'estradiol', 'e2', 'serumestradiol' => 'ESTRADIOL',
            'amh' => 'AMH',
            'ana', 'antinuclearantibodies' => 'ANA',
            'prolactin', 'serumprolactin' => 'PROLACTIN',
            'lhfsh', 'lh', 'fsh' => 'LH, FSH',
            'ddimer' => 'D-DIMER',
            'betahcg', 'hcg' => 'BETA HCG',
            'package1', 'packagei' => 'PACKAGE - 1',
            'package2', 'packageii' => 'PACKAGE - 2',
            'package3', 'packageiii' => 'PACKAGE - 3',
            default => strtoupper($rawLabel),
        };
    }

    /**
     * Parse investigations into structured key-value pairs sorted in standard clinical order
     * with standard clinical units and normal reference ranges.
     *
     * @param mixed $investigations
     * @param Admission|null $admission
     * @return array<int, array{label: string, value: string, unit: string, normal_range: string, reference: string}>
     */
    public static function formatInvestigations($investigations, ?Admission $admission = null): array
    {
        $items = [];

        if ($investigations) {
            if (is_array($investigations)) {
                foreach ($investigations as $k => $v) {
                    $rawLabel = is_string($k) ? trim($k) : 'TEST';
                    $label = self::normalizeInvestigationKey($rawLabel);
                    $val = is_array($v) ? implode(', ', $v) : (string)$v;
                    if ($label === 'BLOOD GROUP' || str_contains($label, 'BLOOD')) {
                        $val = strtoupper($val);
                    }
                    $std = self::INVESTIGATION_STANDARDS[$label] ?? ['unit' => '', 'normal_range' => '', 'reference' => ''];
                    $items[] = [
                        'label' => $label,
                        'value' => $val,
                        'unit' => $std['unit'] ?? '',
                        'normal_range' => $std['normal_range'] ?? '',
                        'reference' => $std['reference'] ?? '',
                    ];
                }
            } else {
                $lines = preg_split('/\r\n|\r|\n/', trim($investigations));
                foreach ($lines as $line) {
                    $line = trim($line, " \t\n\r\0\x0B*•-");
                    if (!$line) {
                        continue;
                    }

                    if (preg_match('/^([^:=]+)[:=](.+)$/', $line, $m)) {
                        $rawLabel = trim($m[1]);
                        $rawVal = trim($m[2]);
                        $label = self::normalizeInvestigationKey($rawLabel);

                        if ($label === 'BLOOD GROUP' || str_contains($label, 'BLOOD')) {
                            $rawVal = strtoupper($rawVal);
                        }

                        $std = self::INVESTIGATION_STANDARDS[$label] ?? ['unit' => '', 'normal_range' => '', 'reference' => ''];

                        $items[] = [
                            'label' => $label,
                            'value' => $rawVal,
                            'unit' => $std['unit'] ?? '',
                            'normal_range' => $std['normal_range'] ?? '',
                            'reference' => $std['reference'] ?? '',
                        ];
                    } else {
                        $items[] = [
                            'label' => 'NOTE',
                            'value' => $line,
                            'unit' => '',
                            'normal_range' => '',
                            'reference' => '',
                        ];
                    }
                }
            }
        }

        if ($admission) {
            $existingLabels = array_column($items, 'label');
            $existingSet = array_flip($existingLabels);

            $bpVal = trim((string)($admission->vital_bp ?: $admission->discharge_bp));
            if ($bpVal !== '' && $bpVal !== '—' && !isset($existingSet['BP'])) {
                $items[] = [
                    'label' => 'BP',
                    'value' => $bpVal,
                    'unit' => 'mmHg',
                    'normal_range' => '120/80',
                    'reference' => '120/80 mmHg',
                ];
            }

            $pulseVal = trim((string)($admission->vital_pulse ?: $admission->discharge_pulse));
            if ($pulseVal !== '' && $pulseVal !== '—' && !isset($existingSet['PULSE'])) {
                $items[] = [
                    'label' => 'PULSE',
                    'value' => $pulseVal,
                    'unit' => 'bpm',
                    'normal_range' => '60 - 100',
                    'reference' => '60 - 100 bpm',
                ];
            }

            $spo2Val = trim((string)($admission->vital_spo2 ?: $admission->discharge_spo2));
            if ($spo2Val !== '' && $spo2Val !== '—' && !isset($existingSet['SPO2'])) {
                $items[] = [
                    'label' => 'SPO2',
                    'value' => $spo2Val,
                    'unit' => '%',
                    'normal_range' => '95 - 100',
                    'reference' => '95 - 100 %',
                ];
            }
        }

        usort($items, function ($a, $b) {
            $orderA = self::INVESTIGATION_ORDER[$a['label']] ?? 99;
            $orderB = self::INVESTIGATION_ORDER[$b['label']] ?? 99;
            return $orderA <=> $orderB;
        });

        return $items;
    }

    /**
     * Parse condition on discharge into structured parameters.
     *
     * @param string|null $text
     * @param Admission|null $admission
     * @return array{general: string, bp: string, pulse: string, spo2: string, pa: string}
     */
    public static function formatConditionOnDischarge(?string $text, ?Admission $admission = null): array
    {
        $res = [
            'general' => '—',
            'bp' => '—',
            'pulse' => '—',
            'spo2' => '—',
            'pa' => '—',
        ];

        if ($admission) {
            if (!empty($admission->discharge_bp)) {
                $bp = trim((string) $admission->discharge_bp);
                if (!str_contains(strtolower($bp), 'mmhg')) {
                    $bp .= ' mmHg';
                }
                $res['bp'] = $bp;
            }
            if (!empty($admission->discharge_pulse)) {
                $pulse = trim((string) $admission->discharge_pulse);
                if (!preg_match('/b\/(?:min|mt)|bpm/i', $pulse)) {
                    $pulse .= ' b/min';
                }
                $res['pulse'] = $pulse;
            }
            if (!empty($admission->discharge_spo2)) {
                $spo2 = trim((string) $admission->discharge_spo2);
                if (!str_contains($spo2, '%')) {
                    $spo2 .= '%';
                }
                $res['spo2'] = $spo2;
            }
        }

        if (!$text || !trim($text)) {
            return $res;
        }

        $matchedAny = false;

        if (preg_match('/general\s+condition\s*[:\-]?\s*([a-zA-Z]+)/i', $text, $m)) {
            $res['general'] = ucfirst(strtolower($m[1]));
            $matchedAny = true;
        }

        if ($res['bp'] === '—' && preg_match('/BP\s*[:\-]?\s*([0-9\/\s]+(?:mmHg)?)/i', $text, $m)) {
            $bp = trim($m[1]);
            if (!str_contains(strtolower($bp), 'mmhg')) {
                $bp .= ' mmHg';
            }
            $res['bp'] = $bp;
            $matchedAny = true;
        }

        if ($res['pulse'] === '—' && preg_match('/Pulse\s*[:\-]?\s*(\d+)\s*(?:b\/mt|b\/min|bpm)?/i', $text, $m)) {
            $res['pulse'] = $m[1] . ' b/min';
            $matchedAny = true;
        }

        if ($res['spo2'] === '—' && preg_match('/SpO2\s*[:\-]?\s*(\d+(?:\s*%)?)/i', $text, $m)) {
            $spo2 = trim($m[1]);
            if (!str_contains($spo2, '%')) {
                $spo2 .= '%';
            }
            $res['spo2'] = $spo2;
            $matchedAny = true;
        }

        if (preg_match('/\bP\/?A\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m)) {
            $paText = trim($m[1]);
            if (preg_match('/no\s+undue\s+bleeding/i', $text) && !preg_match('/no\s+undue\s+bleeding/i', $paText)) {
                $res['pa'] = rtrim($paText, '.') . '; no undue bleeding P/V';
            } else {
                $res['pa'] = $paText;
            }
            $matchedAny = true;
        }

        if (!$matchedAny && $res['general'] === '—') {
            $res['general'] = trim(preg_replace('/\s+/', ' ', $text));
        }

        return $res;
    }

    /**
     * Parse discharge medication orders.
     *
     * @param mixed $text
     * @return array<int, array{medicine: string, frequency: string, duration: string, timing: string}>
     */
    public static function formatMedications($text): array
    {
        if (!$text) {
            return [];
        }

        if (is_array($text)) {
            $formatted = [];
            foreach ($text as $item) {
                if (is_array($item)) {
                    $formatted[] = [
                        'medicine' => trim($item['medicine'] ?? ($item['name'] ?? '—')),
                        'frequency' => trim($item['frequency'] ?? ($item['freq'] ?? '—')),
                        'duration' => trim($item['duration'] ?? ($item['dur'] ?? '—')),
                        'timing' => trim($item['timing'] ?? '—'),
                    ];
                }
            }
            return $formatted;
        }

        if (is_string($text) && str_starts_with(trim($text), '[') && str_ends_with(trim($text), ']')) {
            $decoded = json_decode($text, true);
            if (is_array($decoded)) {
                return self::formatMedications($decoded);
            }
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*•-");
            if (!$line) {
                continue;
            }

            $freq = '—';
            $dur = '—';
            $timing = '—';

            if (preg_match('/\(([\d\-]+)\)/', $line, $fm)) {
                $freq = $fm[1];
                $line = str_replace($fm[0], '', $line);
            } elseif (preg_match('/\b(\d-\d-\d)\b/', $line, $fm)) {
                $freq = $fm[1];
                $line = str_replace($fm[0], '', $line);
            }

            if (preg_match('/(After\s+food|Before\s+food|With\s+food|Bedtime)/i', $line, $tm)) {
                $timing = ucfirst(strtolower($tm[1]));
                $line = str_replace($tm[0], '', $line);
            }

            if (preg_match('/(?:x\s*|for\s*)?(\d+\s*(?:days?|weeks?|months?|nos?|tablets?|tabs|caps))\b/i', $line, $dm)) {
                $dur = strtolower(trim($dm[1]));
                $line = str_replace($dm[0], '', $line);
            }

            $med = trim(preg_replace('/\s+/', ' ', $line));
            $med = trim(preg_replace('/(?:\s*[-–:]+\s*)+$/u', '', $med));
            $med = trim(preg_replace('/\s+x$/i', '', $med));
            $med = trim(preg_replace('/(?:\s*[-–:]+\s*)+$/u', '', $med));

            $med = preg_replace_callback('/\b(tab|cap|syp|inj)\b/i', fn($m) => ucfirst(strtolower($m[0])) . '.', $med);
            $med = str_replace(['Tab..', 'Cap..', 'Inj..', 'Syp..'], ['Tab.', 'Cap.', 'Inj.', 'Syp.'], $med);

            $med = preg_replace_callback('/\+\s*([a-zA-Z]+)/', fn($m) => '+ ' . ucfirst(strtolower($m[1])), $med);
            $med = str_ireplace('Sulbactum', 'Sulbactam', $med);

            $rows[] = [
                'medicine' => $med,
                'frequency' => $freq,
                'duration' => $dur,
                'timing' => $timing,
            ];
        }

        return $rows;
    }

    /**
     * Check if the patient has a stent in situ or placed during procedure.
     *
     * @param Admission|null $admission
     * @param string|null $extraText
     * @return bool
     */
    public static function hasStent(?Admission $admission = null, ?string $extraText = null): bool
    {
        if ($admission && !empty($admission->stent_removal_date)) {
            return true;
        }

        $content = $extraText ?? '';
        if ($admission) {
            $content .= ' ' . ($admission->procedure_name ?? '')
                      . ' ' . ($admission->procedure_done ?? '')
                      . ' ' . ($admission->procedure_note ?? '')
                      . ' ' . ($admission->findings ?? '')
                      . ' ' . ($admission->drain ?? '')
                      . ' ' . ($admission->discharge_advice ?? '')
                      . ' ' . ($admission->follow_up ?? '');
        }
        return (bool) preg_match('/\bstent\b/i', $content);
    }

    /**
     * Normalize any date string (YYYY-MM-DD, DD/MM/YYYY, DD-MM-YYYY) to standard dd.mm.yyyy.
     */
    public static function normalizeToDotDate(?string $dateStr): string
    {
        if (empty($dateStr)) {
            return '';
        }
        $trimmed = trim($dateStr);
        // Normalize YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $trimmed, $m)) {
            return str_pad($m[3], 2, '0', STR_PAD_LEFT) . '.' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '.' . $m[1];
        }
        // Normalize DD/MM/YYYY or DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $trimmed, $m)) {
            $year = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
            return str_pad($m[1], 2, '0', STR_PAD_LEFT) . '.' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '.' . $year;
        }
        return preg_replace('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', '$1.$2.$3', $trimmed);
    }

    /**
     * Resolve the stent removal date string from an Admission model or string.
     */
    public static function resolveStentRemovalDate(Admission|string|null $dateOrAdmission = null): string
    {
        if ($dateOrAdmission instanceof Admission) {
            if ($dateOrAdmission->stent_removal_date) {
                return $dateOrAdmission->stent_removal_date->format('d.m.Y');
            }
            if ($dateOrAdmission->discharge_at) {
                return $dateOrAdmission->discharge_at->copy()->addDays(30)->format('d.m.Y');
            }
            return date('d.m.Y', strtotime('+30 days'));
        }

        return is_string($dateOrAdmission) && !empty($dateOrAdmission)
            ? (self::normalizeToDotDate($dateOrAdmission) ?: '(Date)')
            : '(Date)';
    }

    /**
     * Generate the official hospital English stent removal instruction.
     */
    public static function getStentRemovalNoticeEn(Admission|string|null $dateOrAdmission = null): string
    {
        $d = self::resolveStentRemovalDate($dateOrAdmission);
        return "The DJ Stent placed inside needs to be removed on <strong>{$d}</strong>. Please contact the hospital one day prior to confirm the time.";
    }

    /**
     * Generate the official hospital Tamil stent removal instruction.
     */
    public static function getStentRemovalNoticeTa(Admission|string|null $dateOrAdmission = null): string
    {
        $d = self::resolveStentRemovalDate($dateOrAdmission);
        return "வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு <strong>{$d}</strong> அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.";
    }

    /**
     * Generate the official hospital Tamil stent removal instruction.
     *
     * @param Admission|string|null $dateOrAdmission
     * @return string
     */
    public static function getStentRemovalNotice(Admission|string|null $dateOrAdmission = null): string
    {
        return self::getStentRemovalNoticeTa($dateOrAdmission);
    }

    /**
     * Parse discharge advice and follow-up / review details.
     *
     * @param string|null $adviceText
     * @param string|null $followUpText
     * @param Carbon|null $dischargeAt
     * @param Admission|null $admission
     * @return array{advice_items: array<int, string>, review_text: string, review_date: string, has_stent: bool, tamil_note: string|null}
     */
    public static function formatAdviceAndReview(?string $adviceText, ?string $followUpText, ?Carbon $dischargeAt = null, ?Admission $admission = null): array
    {
        $items = [];
        $reviewText = '';
        $reviewDate = '';

        $source = ($adviceText ?? '') . "\n" . ($followUpText ?? '');
        $lines = preg_split('/\r\n|\r|\n/', trim($source));

        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*•-");
            if (!$line) {
                continue;
            }

            if (preg_match('/review\s+after\s+([^\(\)]+)(?:\((.*?)\))?/i', $line, $m)) {
                $reviewText = 'Review after ' . trim($m[1]);
                if (!empty($m[2])) {
                    $reviewDate = trim($m[2]);
                }
                continue;
            }

            if (preg_match('/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/', $line, $dm)) {
                $reviewDate = self::normalizeToDotDate($dm[1]);
                if (!$reviewText && str_contains(strtolower($line), 'review')) {
                    $reviewText = self::normalizeToDotDate($line);
                    continue;
                }
            }

            if (preg_match('/post\s*natal\s*exercise/i', $line)) {
                $line = 'Post natal exercise';
            } elseif (preg_match('/exclusive\s*breast\s*feeding/i', $line)) {
                $line = 'Exclusive breast feeding for 6 months';
            } elseif (preg_match('/immunisation|immunization/i', $line)) {
                $line = 'Immunisation';
            }

            // Exclude Tamil text from general bullet points (it gets its own box)
            if (!preg_match('/[\x{0B80}-\x{0BFF}]/u', $line)) {
                $items[] = $line;
            }
        }

        if (!$reviewText && !empty(trim((string) $followUpText))) {
            $cleanFollowUp = trim(preg_replace('/[\x{0B80}-\x{0BFF}].*/u', '', (string) $followUpText));
            if ($cleanFollowUp && !preg_match('/^\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4}$/', $cleanFollowUp)) {
                $reviewText = $cleanFollowUp;
            } elseif ($reviewDate) {
                $reviewText = 'Review Date';
            }
        } elseif (!$reviewText && $reviewDate) {
            $reviewText = 'Review Date';
        }

        $hasStent = self::hasStent($admission, $source);
        $tamilNote = null;
        if ($hasStent) {
            if (preg_match('/([\x{0B80}-\x{0BFF}].*)/u', $source, $m)) {
                $tamilNote = trim($m[1]);
            } else {
                $tamilNote = self::getStentRemovalNotice($reviewDate ?: null);
            }
        }

        $items = array_unique(array_filter($items));

        return [
            'advice_items' => array_values($items),
            'review_text' => $reviewText,
            'review_date' => $reviewDate,
            'has_stent' => $hasStent,
            'tamil_note' => $tamilNote,
        ];
    }

    /**
     * Format newborn details into structured cards.
     *
     * @param Admission $admission
     * @return array<int, array{title: string, sex: string, born: string, mode: string, weight: string, apgar: string, neonatal_care: string}>
     */
    public static function formatNewborns(Admission $admission): array
    {
        $newborns = $admission->newborns;
        if ($newborns->isEmpty()) {
            return [];
        }

        $isMultiple = $newborns->count() > 1;
        $cards = [];

        foreach ($newborns as $idx => $baby) {
            $twinLabel = match ($idx) {
                0 => 'Twin A',
                1 => 'Twin B',
                2 => 'Twin C',
                default => 'Baby ' . ($idx + 1),
            };

            $title = $isMultiple ? "Newborn #" . ($idx + 1) . " - " . $twinLabel : "Newborn Details";

            $sex = $baby->sex ? 'Alive ' . strtolower($baby->sex) . ' baby' : '—';

            $bornTime = $baby->birth_at
                ? $baby->birth_at->format('d.m.Y \a\t h:i A')
                : ($admission->procedure_at ? $admission->procedure_at->format('d.m.Y \a\t h:i A') : '—');

            $babyNotes = ($baby->notes ?? '') . ' ' . ($baby->neonatal_care ?? '');
            if (preg_match('/breech/i', $babyNotes)) {
                $mode = 'Breech delivery with episiotomy';
            } elseif (preg_match('/lscs|c-section/i', $babyNotes)) {
                $mode = 'Emergency LSCS under SA';
            } elseif (preg_match('/normal|natural/i', $babyNotes)) {
                $mode = 'Labour natural with episiotomy';
            } else {
                $mode = $admission->procedure_done ?: ($admission->procedure_name ?: '—');
            }

            $weight = $baby->birth_weight_kg ? number_format($baby->birth_weight_kg, 2) . ' kg' : '—';
            $apgar = ($baby->apgar_1_min !== null || $baby->apgar_5_min !== null)
                ? ($baby->apgar_1_min ?? '—') . '/10, ' . ($baby->apgar_5_min ?? '—') . '/10'
                : '—';

            $neonatalCare = $baby->neonatal_care ?: '—';

            $cards[] = [
                'title' => $title,
                'sex' => $sex,
                'born' => $bornTime,
                'mode' => $mode,
                'weight' => $weight,
                'apgar' => $apgar,
                'neonatal_care' => $neonatalCare,
            ];
        }

        return $cards;
    }

    /**
     * Formats surgery performed summary string.
     * Prioritizes procedure_done as the exact operative procedure performed,
     * falling back to procedure_name.
     */
    public static function formatSurgeryPerformed(Admission $admission): string
    {
        $done = trim((string) ($admission->procedure_done ?? ''));
        $name = trim((string) ($admission->procedure_name ?? ''));

        if ($done !== '') {
            return $done;
        }

        if ($name !== '') {
            return $name;
        }

        return '—';
    }

    /**
     * Formats procedure note with bold subheadings for multiple babies or stages.
     */
    public static function formatProcedureNoteHtml(?string $text): string
    {
        if (!$text) {
            return '';
        }

        $html = e($text);
        $html = preg_replace('/(Twin\s+[A-Z]:)/i', '<br><br><strong>$1</strong>', $html);
        $html = preg_replace('/^(<br><br>)/', '', $html);

        return nl2br($html);
    }

    /* =========================================================================
     * SURGICAL / NON-OBSTETRIC FORMATTERS (Mr. Gopalakrishnan Hospital Standard)
     * ========================================================================= */

    /**
     * Parse Past Medical and Surgical History for surgical discharge summaries.
     *
     * @param Admission $admission
     * @return array{dm: string, ht: string, asthma: string, tb: string, other: string, past_surgical: string, allergies: string, medications: string, pulse: string, bp: string}
     */
    public static function formatSurgicalPastHistory(Admission $admission): array
    {
        $text = ($admission->clinical_history ?? '') . "\n" . ($admission->examination ?? '');

        $res = [
            'dm' => 'Nil',
            'ht' => 'Nil',
            'asthma' => 'Nil',
            'tb' => 'Nil',
            'other' => 'Nil',
            'past_surgical' => 'Nil',
            'allergies' => 'Nil',
            'medications' => 'Nil',
            'pulse' => '—',
            'bp' => '—',
        ];

        if (preg_match('/DM\s*[:\-]\s*([^\s\|]+)/i', $text, $m)) $res['dm'] = trim($m[1]);
        if (preg_match('/HT\s*[:\-]\s*([^\s\|]+)/i', $text, $m)) $res['ht'] = trim($m[1]);
        if (preg_match('/Asthma\s*[:\-]\s*([^\s\|]+)/i', $text, $m)) $res['asthma'] = trim($m[1]);
        if (preg_match('/(?:TB|Tuberculosis)\s*[:\-]\s*([^\s\|]+)/i', $text, $m)) $res['tb'] = trim($m[1]);
        if (preg_match('/Other\s*[:\-]\s*([^\s\|]+)/i', $text, $m)) $res['other'] = trim($m[1]);
        if (preg_match('/Past\s*Surgical\s*History\s*[:\-]\s*([^\n\|]+)/i', $text, $m)) $res['past_surgical'] = trim($m[1]);
        if (preg_match('/Allergies\s*[:\-]\s*([^\n\|]+)/i', $text, $m)) $res['allergies'] = trim($m[1]);
        if (preg_match('/Medications\s*[:\-]\s*([^\n\|]+)/i', $text, $m)) $res['medications'] = trim($m[1]);

        if (preg_match('/Pulse\s*[:\-]?\s*(\d+)\s*(?:b\/mt|b\/min|bpm)?/i', $text, $m)) {
            $res['pulse'] = $m[1] . ' b/min';
        }
        if (preg_match('/BP\s*[:\-]?\s*([0-9\/\s]+(?:mmHg|mmhg)?)/i', $text, $m)) {
            $bp = trim($m[1]);
            if (!str_contains(strtolower($bp), 'mmhg')) $bp .= 'mmhg';
            $res['bp'] = $bp;
        }

        return $res;
    }

    /**
     * Parse investigations into split lab groups and imaging (USG / X-Ray / CT)
     * with standard clinical units and normal reference ranges.
     *
     * @param Admission $admission
     * @return array{group1: array<string, string>, group2: array<string, string>, imaging: array<string, string>, units: array<string, string>, ranges: array<string, string>}
     */
    public static function formatSurgicalInvestigations(Admission $admission): array
    {
        $text = $admission->investigations ?? '';
        $lines = preg_split('/\r?\n/', trim($text));

        $group1 = [
            'Hb' => '—',
            'Urea' => '—',
            'Creatinine' => '—',
            'Uric Acid' => '—',
            'RBS' => '—',
        ];

        $group2 = [
            'HBs Ag' => '—',
            'HIV' => '—',
            'HCV' => '—',
            'BG' => '—',
            'ECG' => '—',
        ];

        $units = [
            'Hb' => 'g/dL',
            'Urea' => 'mg/dL',
            'Creatinine' => 'mg/dL',
            'Uric Acid' => 'mg/dL',
            'RBS' => 'mg/dL',
            'HBs Ag' => '',
            'HIV' => '',
            'HCV' => '',
            'BG' => '',
            'ECG' => '',
        ];

        $ranges = [
            'Hb' => '12.0-16.0',
            'Urea' => '15-40',
            'Creatinine' => '0.6-1.2',
            'Uric Acid' => '3.5-7.2',
            'RBS' => '70-140',
            'HBs Ag' => 'Non-React',
            'HIV' => 'Non-React',
            'HCV' => 'Non-React',
            'BG' => 'Compatible',
            'ECG' => 'Normal Sinus',
        ];

        $imaging = [];

        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*•-");
            if (!$line) continue;

            if (preg_match('/^([^:=]+)[:=](.+)$/', $line, $m)) {
                $rawK = trim($m[1]);
                $v = trim($m[2]);
                $cleanK = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $rawK));

                if (in_array($cleanK, ['hb', 'haemoglobin'])) $group1['Hb'] = $v;
                elseif ($cleanK === 'urea') $group1['Urea'] = $v;
                elseif (in_array($cleanK, ['creatinine', 'creatine'])) $group1['Creatinine'] = $v;
                elseif ($cleanK === 'uricacid') $group1['Uric Acid'] = $v;
                elseif ($cleanK === 'rbs') $group1['RBS'] = $v;
                elseif (in_array($cleanK, ['hbsag', 'hbs'])) $group2['HBs Ag'] = str_ireplace('negative', 'Neg', $v);
                elseif ($cleanK === 'hiv') $group2['HIV'] = str_ireplace('negative', 'Neg', $v);
                elseif ($cleanK === 'hcv') $group2['HCV'] = str_ireplace('negative', 'Neg', $v);
                elseif (in_array($cleanK, ['bg', 'bloodgroup'])) $group2['BG'] = str_replace(' ', '', $v);
                elseif (in_array($cleanK, ['ecg', 'ekg', 'electrocardiogram'])) $group2['ECG'] = $v;
                elseif (preg_match('/usg|x-?ray|ct|mri|biopsy|histopath/i', $rawK)) {
                    $imaging[strtoupper($rawK)] = $v;
                }
            }
        }

        return [
            'group1' => $group1,
            'group2' => $group2,
            'imaging' => $imaging,
            'units' => $units,
            'ranges' => $ranges,
        ];
    }

    /**
     * Parse surgical discharge advice, implants, exercise, contact, and Tamil stent note.
     *
     * @param Admission $admission
     * @return array{implant: string, diet: string, exercise: string, emergency_contact: string, when_to_contact: string, review: string, tamil_note: string}
     */
    public static function formatSurgicalAdvice(Admission $admission): array
    {
        $text = ($admission->discharge_advice ?? '') . "\n" . ($admission->follow_up ?? '');
        $hasStent = self::hasStent($admission, $text);

        $res = [
            'implant' => $hasStent ? 'DJ Stent' : 'Nil',
            'diet' => $admission->post_surgery_diet ?: '—',
            'exercise' => $admission->post_surgery_activity ?: '—',
            'emergency_contact' => 'Reception: 04342-262266, 7418084266',
            'when_to_contact' => 'Report immediately if bleeding, fever, or severe pain',
            'review' => $admission->suture_removal_date ? 'Review on ' . $admission->suture_removal_date->format('d.m.Y') . ' for Suture Removal' : '',
            'tamil_note' => '',
        ];

        if (preg_match('/Implant\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m)) $res['implant'] = trim($m[1]);
        if (preg_match('/Diet\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m) && empty($admission->post_surgery_diet)) $res['diet'] = trim($m[1]);
        if (preg_match('/Exercise\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m) && empty($admission->post_surgery_activity)) $res['exercise'] = trim($m[1]);
        if (preg_match('/Emergency\s*Contact\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m)) $res['emergency_contact'] = trim($m[1]);
        if (preg_match('/When\s*to\s*Contact\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m)) $res['when_to_contact'] = trim($m[1]);
        if (preg_match('/Review\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m)) $res['review'] = trim($m[1]);
        if (!empty($admission->follow_up)) {
            $res['review'] = $admission->follow_up;
        }
        if (preg_match('/([\x{0B80}-\x{0BFF}].*)/u', $text, $m)) {
            $res['tamil_note'] = trim($m[1]);
        } elseif ($hasStent) {
            $date = null;
            if ($admission->stent_removal_date) {
                $date = $admission->stent_removal_date->format('d.m.Y');
            } elseif (preg_match('/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/', $res['review'] ?: ($admission->follow_up ?? ''), $dm)) {
                $date = self::normalizeToDotDate($dm[1]);
            }
            $res['tamil_note'] = self::getStentRemovalNotice($date);
        }

        return $res;
    }

    /**
     * Parse surgical drugs table with columns: DRUG, DOSAGE, NO.
     *
     * @param string|null $text
     * @return array<int, array{drug: string, dosage: string, no: string}>
     */
    public static function formatSurgicalDrugs(?string $text): array
    {
        if (!$text || !trim($text)) {
            return [];
        }

        $lines = preg_split('/\r?\n/', trim($text));
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*•-");
            if (!$line) continue;

            if (preg_match('/review\s+after\s*([^\(\)]+)(?:\((.*?)\))?/i', $line, $m)) {
                $rows[] = [
                    'drug' => 'Review after ' . trim($m[1]),
                    'dosage' => !empty($m[2]) ? trim($m[2]) : '—',
                    'no' => '',
                ];
                continue;
            }

            $no = '—';
            if (preg_match('/(\d+)\s*(?:nos|no|tablets|tabs|caps)\b/i', $line, $nm)) {
                $no = $nm[1];
                $line = str_replace($nm[0], '', $line);
            }

            $dosage = '—';
            if (preg_match('/\(([\d\-]+)\)/', $line, $dm)) {
                $dosage = $dm[1];
                $line = str_replace($dm[0], '', $line);
            } elseif (preg_match('/\b(\d-\d-\d)\b/', $line, $dm)) {
                $dosage = $dm[1];
                $line = str_replace($dm[0], '', $line);
            }

            if (preg_match('/[-–]?\s*(After\s+food|Before\s+food|With\s+food)/i', $line, $tm)) {
                $line = str_replace($tm[0], '', $line);
            }

            $drug = trim(preg_replace('/[-–x\s]+$/', '', trim($line)));
            $drug = preg_replace('/\s+/', ' ', $drug);

            $rows[] = [
                'drug' => $drug,
                'dosage' => $dosage,
                'no' => $no,
            ];
        }

        return $rows;
    }

    /**
     * Extract clinical history subsections (complaints, HPI, past history, hospital course)
     * without inventing or dropping clinical text.
     *
     * @param Admission $admission
     * @return array{complaints: string, hpi: string, has_past_history: bool, past_history: array<string, string>, hospital_course: string}
     */
    public static function formatClinicalSections(Admission $admission): array
    {
        $rawHistory = trim((string) ($admission->clinical_history ?? ''));
        $rawExam = trim((string) ($admission->examination ?? ''));
        $combined = $rawHistory . "\n" . $rawExam;

        $hasPastHistory = (bool) preg_match(
            '/(Past\s+(?:Medical|Surgical)\s+History|Allergies|DM\s*[:\-]|HT\s*[:\-]|Asthma\s*[:\-]|Tuberculosis\s*[:\-]|TB\s*[:\-])/i',
            $combined
        );
        $pastHistory = self::formatSurgicalPastHistory($admission);

        $complaintsLines = [];
        $hpiLines = [];
        $otherLines = [];

        foreach (preg_split('/\r\n|\r|\n/', $rawHistory) as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            // Structured Past History lines already captured in Section 7 (unless no DM/HT pattern matches)
            if (preg_match('/^(Past\s+Medical\s+History|Past\s+Surgical\s+History|Allergies\s*[:\-]|Medications\s*[:\-]|Pulse\s*[:\-].*BP\s*[:\-])/i', $trimmed)) {
                continue;
            }
            if (preg_match('/^(?:C\/O|Chief\s+Complaints?|Presenting\s+Complaints?)\b/i', $trimmed)) {
                $complaintsLines[] = $trimmed;
            } else {
                $otherLines[] = $trimmed;
            }
        }

        if (empty($complaintsLines) && !empty($otherLines)) {
            $firstLine = array_shift($otherLines);
            if (empty($otherLines) && preg_match('/^([^.!?]+[.!?])\s+(.+)$/', $firstLine, $sm)) {
                $complaintsLines[] = trim($sm[1]);
                $otherLines[] = trim($sm[2]);
            } else {
                $complaintsLines[] = $firstLine;
            }
        }

        $hpiLines = $otherLines;

        // Preserve clinical examination free of standalone extracted Past History lines if needed
        $hospitalCourse = trim((string) ($admission->treatment_course ?? ''));

        return [
            'complaints' => implode("\n", $complaintsLines) ?: '—',
            'hpi' => implode("\n", $hpiLines) ?: '—',
            'has_past_history' => $hasPastHistory,
            'past_history' => $pastHistory,
            'hospital_course' => $hospitalCourse ?: '—',
        ];
    }
}
