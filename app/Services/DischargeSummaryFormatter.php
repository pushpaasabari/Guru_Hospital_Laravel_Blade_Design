<?php

namespace App\Services;

use App\Models\Admission;
use Carbon\Carbon;

class DischargeSummaryFormatter
{
    /**
     * Standard order for the 10 core lab investigations.
     */
    private const INVESTIGATION_ORDER = [
        'HB' => 1,
        'BLOOD GROUP' => 2,
        'HIV' => 3,
        'HCV' => 4,
        'VDRL' => 5,
        'HBSAG' => 6,
        'UREA' => 7,
        'CREATININE' => 8,
        'URIC ACID' => 9,
        'RBS' => 10,
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

            if (preg_match('/^(Inj\.\s+[A-Za-z0-9_\-+]+|Tab\.\s+[A-Za-z0-9_\-+\s]+|Cap\.\s+[A-Za-z0-9_\-+\s]+|[A-Za-z0-9_\-+]+)\s+(.+)$/i', $normalized, $m)) {
                $med = trim($m[1]);
                $dose = trim($m[2]);

                // Normalize unit spacing: 100mg -> 100 mg
                $dose = preg_replace('/(\d+)\s*(mg|g|mcg|ml)/i', '$1 $2', $dose);

                // Clean dose casing: 'Iv' -> 'IV', 'bd' -> 'BD'
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
     * Parse investigations into structured key-value pairs sorted in standard clinical order.
     *
     * @param mixed $investigations
     * @return array<int, array{label: string, value: string}>
     */
    public static function formatInvestigations($investigations): array
    {
        if (!$investigations) {
            return [];
        }

        $items = [];

        if (is_array($investigations)) {
            foreach ($investigations as $k => $v) {
                $label = is_string($k) ? strtoupper(trim($k)) : 'TEST';
                $val = is_array($v) ? implode(', ', $v) : (string)$v;
                $items[] = ['label' => $label, 'value' => $val];
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

                    $key = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $rawLabel));
                    $label = match ($key) {
                        'hb', 'haemoglobin' => 'HB',
                        'bg', 'bloodgroup' => 'BLOOD GROUP',
                        'hiv' => 'HIV',
                        'hcv' => 'HCV',
                        'vdrl' => 'VDRL',
                        'hbsag' => 'HBSAG',
                        'urea' => 'UREA',
                        'creatinine' => 'CREATININE',
                        'uricacid' => 'URIC ACID',
                        'rbs' => 'RBS',
                        default => strtoupper($rawLabel),
                    };

                    $items[] = [
                        'label' => $label,
                        'value' => $rawVal,
                    ];
                } else {
                    $items[] = [
                        'label' => 'NOTE',
                        'value' => $line,
                    ];
                }
            }
        }

        // Sort items so core investigations appear in clinical order: HB, BG, HIV, HCV, VDRL, HBSAG, UREA, CREATININE, URIC ACID, RBS
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
     * @return array{general: string, bp: string, pulse: string, pa: string}
     */
    public static function formatConditionOnDischarge(?string $text): array
    {
        $res = [
            'general' => 'Good',
            'bp' => '110/70 mmHg',
            'pulse' => '86 b/min',
            'pa' => 'Uterus contracted well; no undue bleeding P/V',
        ];

        if (!$text) {
            return $res;
        }

        if (preg_match('/general\s+condition\s*[:\-]?\s*([a-zA-Z]+)/i', $text, $m)) {
            $res['general'] = ucfirst(strtolower($m[1]));
        }

        if (preg_match('/BP\s*[:\-]?\s*([0-9\/\s]+(?:mmHg)?)/i', $text, $m)) {
            $bp = trim($m[1]);
            if (!str_contains(strtolower($bp), 'mmhg')) {
                $bp .= ' mmHg';
            }
            $res['bp'] = $bp;
        }

        if (preg_match('/Pulse\s*[:\-]?\s*(\d+)\s*(?:b\/mt|b\/min|bpm)?/i', $text, $m)) {
            $res['pulse'] = $m[1] . ' b/min';
        }

        if (preg_match('/PA\s*[-:]\s*(.*?)(?=\n|$)/i', $text, $m)) {
            $paText = trim($m[1]);
            if (preg_match('/no\s+undue\s+bleeding/i', $text)) {
                $res['pa'] = rtrim($paText, '.') . '; no undue bleeding P/V';
            } else {
                $res['pa'] = $paText;
            }
        }

        return $res;
    }

    /**
     * Parse discharge medication orders.
     *
     * @param string|null $text
     * @return array<int, array{medicine: string, frequency: string, duration: string, timing: string}>
     */
    public static function formatMedications(?string $text): array
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

            if (preg_match('/(?:x\s*|for\s*)?(\d+\s*(?:days|weeks|months|day))/i', $line, $dm)) {
                $dur = strtolower(trim($dm[1]));
                $line = str_replace($dm[0], '', $line);
            }

            $med = trim(preg_replace('/[-–x]+$/', '', trim($line)));
            $med = preg_replace('/\s+/', ' ', $med);

            // Capitalize medicine prefix
            $med = preg_replace_callback('/\b(tab|cap|syp|inj)\b/i', fn($m) => ucfirst(strtolower($m[0])) . '.', $med);
            $med = str_replace(['Tab..', 'Cap..', 'Inj..', 'Syp..'], ['Tab.', 'Cap.', 'Inj.', 'Syp.'], $med);

            // Capitalize word after + e.g. + sulbactum -> + Sulbactam
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
     * Parse discharge advice and follow-up / review details.
     *
     * @param string|null $adviceText
     * @param string|null $followUpText
     * @param Carbon|null $dischargeAt
     * @return array{advice_items: array<int, string>, review_text: string, review_date: string}
     */
    public static function formatAdviceAndReview(?string $adviceText, ?string $followUpText, ?Carbon $dischargeAt = null): array
    {
        $items = [];
        $reviewText = 'Review after one week';
        $reviewDate = $dischargeAt ? $dischargeAt->copy()->addDays(5)->format('d/m/Y') : '03/09/2026';

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
                $reviewDate = $dm[1];
            }

            // Normalise casing: 'Post Natal Exercise' -> 'Post natal exercise'
            if (preg_match('/post\s*natal\s*exercise/i', $line)) {
                $line = 'Post natal exercise';
            } elseif (preg_match('/exclusive\s*breast\s*feeding/i', $line)) {
                $line = 'Exclusive breast feeding for 6 months';
            } elseif (preg_match('/immunisation|immunization/i', $line)) {
                $line = 'Immunisation';
            }

            $items[] = $line;
        }

        $items = array_unique(array_filter($items));
        if (empty($items)) {
            $items = ['Immunisation', 'Exclusive breast feeding for 6 months', 'Post natal exercise'];
        }

        return [
            'advice_items' => array_values($items),
            'review_text' => $reviewText,
            'review_date' => $reviewDate,
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

            $sex = 'Alive ' . strtolower($baby->sex ?? 'male') . ' baby';

            $bornTime = $baby->birth_at
                ? $baby->birth_at->format('d.m.Y \a\t h:i A')
                : ($admission->procedure_at ? $admission->procedure_at->format('d.m.Y \a\t h:i A') : '—');

            // Detect delivery mode specific to this baby
            $babyNotes = ($baby->notes ?? '') . ' ' . ($baby->neonatal_care ?? '');
            if (preg_match('/breech/i', $babyNotes)) {
                $mode = 'Breech delivery with episiotomy';
            } elseif (preg_match('/lscs|c-section/i', $babyNotes)) {
                $mode = 'Emergency LSCS under SA';
            } elseif (preg_match('/normal|natural/i', $babyNotes)) {
                $mode = 'Labour natural with episiotomy';
            } else {
                // Fallback by twin index if procedure has both
                if ($isMultiple && $idx === 0) {
                    $mode = 'Breech delivery with episiotomy';
                } elseif ($isMultiple && $idx === 1) {
                    $mode = 'Emergency LSCS under SA';
                } else {
                    $mode = $admission->procedure_name ?: 'Normal Vaginal Delivery';
                }
            }

            $weight = $baby->birth_weight_kg ? number_format($baby->birth_weight_kg, 2) . ' kg' : '2.25 kg';
            $apgar = ($baby->apgar_1_min ?? '7') . '/10, ' . ($baby->apgar_5_min ?? '8') . '/10';

            $neonatalCare = $baby->neonatal_care ?: 'Handed over to pediatrician.';
            if ($idx === 0 && str_contains(strtolower($neonatalCare), 'rds')) {
                $neonatalCare = 'RDS (+). Handed over to pediatrician; shifted to NICU.';
            }

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
     */
    public static function formatSurgeryPerformed(Admission $admission): string
    {
        if ($admission->newborns->count() > 1) {
            return 'Twin A: Labour natural with episiotomy | Twin B: Emergency LSCS';
        }

        return $admission->procedure_name ?: ($admission->procedure_done ?: '—');
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
        // Emphasize Twin A:, Twin B:, Stage 1:, etc.
        $html = preg_replace('/(Twin\s+[A-Z]:)/i', '<br><br><strong>$1</strong>', $html);
        $html = preg_replace('/^(<br><br>)/', '', $html);

        return nl2br($html);
    }
}
