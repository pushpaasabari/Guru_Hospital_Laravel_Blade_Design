<?php

namespace App\Models;

use App\Services\DischargeSummaryFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

class ClinicalDropdownOption extends Model
{
    use HasFactory;

    public const TYPE_MEDICATION = 'medication';
    public const TYPE_POSTOP_ORDER = 'postop_order';

    public const ALLOWED_TYPES = [
        self::TYPE_MEDICATION,
        self::TYPE_POSTOP_ORDER,
    ];

    public const CACHE_KEY_PREFIX = 'clinical_dropdown_options.v1.';

    /**
     * Built-in common discharge medications preserved as baseline catalog.
     */
    public const DEFAULT_MEDICATIONS = [
        'Tab. Cefixime + Sulbactam',
        'Tab. Cefixime 200mg',
        'Tab. Acendol-P (Aceclofenac + Paracetamol)',
        'Cap. Cyra-D (Rabeprazole + Domperidone)',
        'Tab. Clavam 625mg (Amoxicillin + Clavulanate)',
        'Tab. Zerodol-P',
        'Tab. Zerodol-SP',
        'Tab. Paracetamol 650mg',
        'Tab. Pantoprazole 40mg',
        'Tab. Pantocid-D',
        'Tab. Metrogyl 400mg',
        'Tab. Alonac-SP',
        'Tab. Niftas 100mg',
        'Tab. Silodosin 8mg',
        'Tab. Iron & Folic Acid',
        'Tab. Shelcal 500mg (Calcium)',
        'Syp. Cremaffin',
        'Inj. Pipzo 4.5g',
        'Inj. Metrogyl 100mg',
        'Inj. Pan 40',
    ];

    /**
     * Built-in grouped immediate postoperative orders preserved as baseline catalog.
     */
    public const DEFAULT_POSTOP_ORDER_GROUPS = [
        'Post-Op Medications & Injections' => [
            'Inj - Cefbact 1.5g BD IV',
            'Inj - Diclo IM (sos)',
            'Inj - Metrogyl 100mg TDS IV',
            'Inj - Pan 40mg IV BD',
            'Inj. Tramadol 50mg IV SOS (severe pain)',
            'Inj. Ondansetron 4mg IV SOS (vomiting)',
            'Inj - Oxytocin 10 IU in 500ml RL (post-LSCS)',
        ],
        'IV Fluids' => [
            'IV Fluids: RL @ 100 ml/hr',
            'IV Fluids: DNS @ 100 ml/hr',
            'IV Fluids: NS 500ml @ 75 ml/hr',
        ],
        'Nursing & Patient Care Orders' => [
            'NPO for 6 hours',
            'Sips of water after 6 hours if bowel sounds present',
            'Monitor vitals q1h x 4h, then q4h (BP, Pulse, SpO2, Temp)',
            'Catheter care & monitor hourly urine output',
            'Check dressing site for soakage / bleeding',
            'Semi-fowler position',
        ],
    ];

    protected $table = 'clinical_dropdown_options';

    protected $fillable = [
        'type',
        'group_label',
        'label',
        'normalized_key',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        $flush = static function (self $option): void {
            Cache::forget(self::CACHE_KEY_PREFIX . $option->type);
        };

        static::saved($flush);
        static::deleted($flush);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('label');
    }

    public static function cleanLabel(string $label): string
    {
        return trim((string) preg_replace('/\s+/', ' ', trim($label)));
    }

    public static function normalizeKey(string $label): string
    {
        return mb_strtolower(self::cleanLabel($label), 'UTF-8');
    }

    public static function isValidOptionLabel(string $label): bool
    {
        $clean = self::cleanLabel($label);
        if ($clean === '' || mb_strlen($clean, 'UTF-8') > 300) {
            return false;
        }

        if (str_starts_with($clean, '[') || str_starts_with($clean, '{')) {
            return false;
        }

        $key = self::normalizeKey($clean);
        if (in_array($key, ['-', '--', '—', 'na', 'n/a', 'nil', 'none'], true)) {
            return false;
        }

        if (preg_match('/^\d+$/', $key)) {
            return false;
        }

        return (bool) preg_match('/[a-zA-Z0-9]/', $clean);
    }

    public static function defaultCustomGroupFor(string $type): string
    {
        return $type === self::TYPE_POSTOP_ORDER
            ? 'Saved Hospital Orders (Custom)'
            : 'Saved Hospital Medications (Custom)';
    }

    /**
     * Built-in default catalog keyed by normalized option text.
     *
     * @return array<string, array{label: string, group_label: string}>
     */
    public static function builtInMapFor(string $type): array
    {
        $map = [];

        if ($type === self::TYPE_MEDICATION) {
            foreach (self::DEFAULT_MEDICATIONS as $label) {
                $map[self::normalizeKey($label)] = [
                    'label' => $label,
                    'group_label' => 'Standard Discharge Medications',
                ];
            }
            return $map;
        }

        if ($type === self::TYPE_POSTOP_ORDER) {
            foreach (self::DEFAULT_POSTOP_ORDER_GROUPS as $group => $items) {
                foreach ($items as $label) {
                    $map[self::normalizeKey($label)] = [
                        'label' => $label,
                        'group_label' => $group,
                    ];
                }
            }
        }

        return $map;
    }

    /**
     * Grouped options merged from active saved custom items + built-in defaults.
     *
     * @return array<int, array{group: string, is_custom: bool, options: array<int, string>}>
     */
    public static function groupedOptionsFor(string $type): array
    {
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            return [];
        }

        return Cache::remember(
            self::CACHE_KEY_PREFIX . $type,
            now()->addHours(12),
            function () use ($type): array {
                $seenKeys = [];
                $groups = [];

                $builtInMap = self::builtInMapFor($type);
                foreach (array_keys($builtInMap) as $key) {
                    $seenKeys[$key] = true;
                }

                $customByGroup = [];
                $customRecords = static::query()
                    ->ofType($type)
                    ->active()
                    ->get();

                foreach ($customRecords as $record) {
                    $key = $record->normalized_key ?: self::normalizeKey($record->label);
                    if (isset($seenKeys[$key])) {
                        continue;
                    }
                    $seenKeys[$key] = true;
                    $groupName = $record->group_label ?: self::defaultCustomGroupFor($type);
                    $customByGroup[$groupName][] = $record->label;
                }

                // Surface newly added hospital items first so repeat entry is instant.
                foreach ($customByGroup as $groupName => $labels) {
                    $groups[] = [
                        'group' => $groupName,
                        'is_custom' => true,
                        'options' => array_values($labels),
                    ];
                }

                if ($type === self::TYPE_MEDICATION) {
                    $groups[] = [
                        'group' => 'Standard Discharge Medications',
                        'is_custom' => false,
                        'options' => self::DEFAULT_MEDICATIONS,
                    ];
                } elseif ($type === self::TYPE_POSTOP_ORDER) {
                    foreach (self::DEFAULT_POSTOP_ORDER_GROUPS as $groupName => $items) {
                        $groups[] = [
                            'group' => $groupName,
                            'is_custom' => false,
                            'options' => $items,
                        ];
                    }
                }

                return $groups;
            }
        );
    }

    /**
     * Flat deduplicated option list (saved custom items first, followed by built-in defaults).
     *
     * @return array<int, string>
     */
    public static function flatOptionsFor(string $type): array
    {
        $flat = [];
        foreach (self::groupedOptionsFor($type) as $group) {
            foreach ($group['options'] as $opt) {
                $flat[] = $opt;
            }
        }
        return array_values(array_unique($flat));
    }

    /**
     * Save or activate one option if it is valid and not already in built-in defaults or DB.
     *
     * @return array{id: int|null, label: string, group_label: string, created: bool, is_default: bool}|null
     */
    public static function rememberOne(
        string $type,
        string $label,
        ?int $userId = null,
        ?string $groupLabel = null
    ): ?array {
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            return null;
        }

        $clean = self::cleanLabel($label);
        if (!self::isValidOptionLabel($clean)) {
            return null;
        }

        $key = self::normalizeKey($clean);
        $builtInMap = self::builtInMapFor($type);
        if (isset($builtInMap[$key])) {
            return [
                'id' => null,
                'label' => $builtInMap[$key]['label'],
                'group_label' => $builtInMap[$key]['group_label'],
                'created' => false,
                'is_default' => true,
            ];
        }

        $existing = static::query()
            ->ofType($type)
            ->where('normalized_key', $key)
            ->first();

        if ($existing) {
            $reactivated = false;
            if (!$existing->is_active) {
                $existing->is_active = true;
                $existing->save();
                $reactivated = true;
            }

            return [
                'id' => $existing->id,
                'label' => $existing->label,
                'group_label' => $existing->group_label ?: self::defaultCustomGroupFor($type),
                'created' => $reactivated,
                'is_default' => false,
            ];
        }

        $resolvedGroup = $groupLabel ? self::cleanLabel($groupLabel) : self::defaultCustomGroupFor($type);
        $nextOrder = (int) (static::query()->ofType($type)->max('sort_order') ?? 0) + 1;

        try {
            $record = static::create([
                'type' => $type,
                'group_label' => $resolvedGroup,
                'label' => $clean,
                'normalized_key' => $key,
                'is_active' => true,
                'sort_order' => $nextOrder,
                'created_by' => $userId,
            ]);

            return [
                'id' => $record->id,
                'label' => $record->label,
                'group_label' => $record->group_label ?: self::defaultCustomGroupFor($type),
                'created' => true,
                'is_default' => false,
            ];
        } catch (QueryException $e) {
            $concurrent = static::query()
                ->ofType($type)
                ->where('normalized_key', $key)
                ->first();

            if ($concurrent) {
                return [
                    'id' => $concurrent->id,
                    'label' => $concurrent->label,
                    'group_label' => $concurrent->group_label ?: self::defaultCustomGroupFor($type),
                    'created' => false,
                    'is_default' => false,
                ];
            }

            throw $e;
        }
    }

    /**
     * Save any newly entered valid options from a list and return count newly persisted.
     *
     * @param array<int, string> $labels
     */
    public static function rememberMany(
        string $type,
        array $labels,
        ?int $userId = null,
        ?string $groupLabel = null
    ): int {
        $createdCount = 0;

        foreach ($labels as $rawLabel) {
            if (!is_string($rawLabel)) {
                continue;
            }
            $result = self::rememberOne($type, $rawLabel, $userId, $groupLabel);
            if ($result && ($result['created'] ?? false)) {
                $createdCount++;
            }
        }

        return $createdCount;
    }

    /**
     * Extract clean medication names from Stage 5 serialized or free-text medication orders.
     *
     * @return array<int, string>
     */
    public static function extractMedicationNames(?string $rawText): array
    {
        if (!$rawText || trim($rawText) === '') {
            return [];
        }

        $trimmed = trim($rawText);
        if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
            $rows = DischargeSummaryFormatter::formatMedications($trimmed);
            return array_values(array_filter(array_map(
                fn (array $row): string => self::cleanLabel((string) ($row['medicine'] ?? '')),
                $rows
            )));
        }

        $lines = preg_split('/\r\n|\r|\n/', $trimmed) ?: [];
        $names = [];

        foreach ($lines as $line) {
            $lineTrimmed = trim($line);
            if ($lineTrimmed === '') {
                continue;
            }

            // Preserve exact casing/formatting from Discharge.tsx serializer:
            // "* Medicine Name (Frequency) x Duration - Timing"
            if (preg_match('/^\*\s*(.+?)\s+\([^()]+\)\s+x\s+.+?\s+-\s+[^\r\n-]+$/u', $lineTrimmed, $matches)) {
                $med = self::cleanLabel($matches[1]);
                if ($med !== '') {
                    $names[] = $med;
                }
                continue;
            }

            foreach (DischargeSummaryFormatter::formatMedications($lineTrimmed) as $formatted) {
                $med = self::cleanLabel((string) ($formatted['medicine'] ?? ''));
                if ($med !== '') {
                    $names[] = $med;
                }
            }
        }

        return $names;
    }

    /**
     * Extract full postoperative order lines from Stage 4 numbered/bulleted text.
     *
     * @return array<int, string>
     */
    public static function extractPostopOrderLines(?string $rawText): array
    {
        if (!$rawText || trim($rawText) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($rawText)) ?: [];
        $orders = [];

        foreach ($lines as $line) {
            $cleaned = trim($line);
            if ($cleaned === '') {
                continue;
            }

            // Strip leading numbering/bullets emitted by Surgery.tsx ("1. ...", "* ...", "• ...")
            $cleaned = (string) preg_replace('/^(?:(?:[*•\-]+|#?\d+[\.\)\-:]|\(\d+\))\s*)+/', '', $cleaned);
            $cleaned = self::cleanLabel($cleaned);
            if ($cleaned !== '') {
                $orders[] = $cleaned;
            }
        }

        return $orders;
    }
}
