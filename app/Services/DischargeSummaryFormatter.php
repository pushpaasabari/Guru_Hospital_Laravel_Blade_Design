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
                        'creatinine', 'creatine' => 'CREATININE',
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
            'general' => '—',
            'bp' => '—',
            'pulse' => '—',
            'pa' => '—',
        ];

        if (!$text || !trim($text)) {
            return $res;
        }

        $matchedAny = false;

        if (preg_match('/general\s+condition\s*[:\-]?\s*([a-zA-Z]+)/i', $text, $m)) {
            $res['general'] = ucfirst(strtolower($m[1]));
            $matchedAny = true;
        }

        if (preg_match('/BP\s*[:\-]?\s*([0-9\/\s]+(?:mmHg)?)/i', $text, $m)) {
            $bp = trim($m[1]);
            if (!str_contains(strtolower($bp), 'mmhg')) {
                $bp .= ' mmHg';
            }
            $res['bp'] = $bp;
            $matchedAny = true;
        }

        if (preg_match('/Pulse\s*[:\-]?\s*(\d+)\s*(?:b\/mt|b\/min|bpm)?/i', $text, $m)) {
            $res['pulse'] = $m[1] . ' b/min';
            $matchedAny = true;
        }

        if (preg_match('/PA\s*[-:]\s*(.*?)(?=\n|$)/i', $text, $m)) {
            $paText = trim($m[1]);
            if (preg_match('/no\s+undue\s+bleeding/i', $text)) {
                $res['pa'] = rtrim($paText, '.') . '; no undue bleeding P/V';
            } else {
                $res['pa'] = $paText;
            }
            $matchedAny = true;
        }

        if (!$matchedAny) {
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
     * Generate the official hospital Tamil stent removal instruction.
     *
     * @param string|null $date
     * @return string
     */
    public static function getStentRemovalNotice(?string $date = null): string
    {
        $d = $date ?: '(Date)';
        return "வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு {$d} அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.";
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
                $reviewDate = $dm[1];
                if (!$reviewText && str_contains(strtolower($line), 'review')) {
                    $reviewText = $line;
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
                $mode = $admission->procedure_name ?: ($admission->procedure_done ?: '—');
            }

            $weight = $baby->birth_weight_kg ? number_format($baby->birth_weight_kg, 2) . ' kg' : '—';
            $apgar = ($baby->apgar_1_min !== null || $baby->apgar_5_min !== null)
                ? ($baby->apgar_1_min ?? '—') . '/10, ' . ($baby->apgar_5_min ?? '—') . '/10'
                : '—';

            $neonatalCare = $baby->neonatal_care ?: '—';
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
     * Parse investigations into split lab groups and imaging (USG / X-Ray / CT).
     *
     * @param Admission $admission
     * @return array{group1: array<string, string>, group2: array<string, string>, imaging: array<string, string>}
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
                elseif (preg_match('/usg|x-?ray|ct|mri|biopsy|histopath/i', $rawK)) {
                    $imaging[strtoupper($rawK)] = $v;
                }
            }
        }

        return [
            'group1' => $group1,
            'group2' => $group2,
            'imaging' => $imaging,
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
            'diet' => '—',
            'exercise' => '—',
            'emergency_contact' => 'Reception: 04342-262266, 7418084266',
            'when_to_contact' => 'Report immediately if bleeding, fever, or severe pain',
            'review' => '',
            'tamil_note' => '',
        ];

        if (preg_match('/Implant\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m)) $res['implant'] = trim($m[1]);
        if (preg_match('/Diet\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m)) $res['diet'] = trim($m[1]);
        if (preg_match('/Exercise\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m)) $res['exercise'] = trim($m[1]);
        if (preg_match('/Emergency\s*Contact\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m)) $res['emergency_contact'] = trim($m[1]);
        if (preg_match('/When\s*to\s*Contact\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m)) $res['when_to_contact'] = trim($m[1]);
        if (preg_match('/Review\s*[:\-]\s*(.*?)(?=\n|$)/i', $text, $m)) $res['review'] = trim($m[1]);
        if (preg_match('/([\x{0B80}-\x{0BFF}].*)/u', $text, $m)) {
            $res['tamil_note'] = trim($m[1]);
        } elseif ($hasStent) {
            $date = null;
            if (preg_match('/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/', $res['review'] ?: ($admission->follow_up ?? ''), $dm)) {
                $date = $dm[1];
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
