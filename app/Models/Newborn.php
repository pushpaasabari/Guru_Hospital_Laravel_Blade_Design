<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Newborn extends Model
{
    public const BIRTH_VACCINES = ['Vitamin K', 'BCG', 'OPV', 'HEP-B1'];

    protected $fillable = [
        'admission_id', 'sex', 'birth_at', 'birth_weight_kg', 'discharge_weight_kg',
        'apgar_1_min', 'apgar_5_min', 'neonatal_care', 'notes',
        'birth_vaccines', 'vaccine_date', 'vaccine_dates',
    ];

    protected $casts = [
        'birth_at' => 'datetime:Y-m-d\TH:i:s',
        'birth_weight_kg' => 'float',
        'discharge_weight_kg' => 'float',
        'birth_vaccines' => 'array',
        'vaccine_date' => 'date:Y-m-d',
        'vaccine_dates' => 'array',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    /**
     * Format selected birth vaccines grouped cleanly by administration date.
     */
    public static function formatBirthVaccinesText(?array $vaccines, ?array $vaccineDates = null, mixed $fallbackDate = null): string
    {
        $selected = array_values(array_intersect(
            self::BIRTH_VACCINES,
            array_filter((array) $vaccines)
        ));

        $fallbackFormatted = '';
        if ($fallbackDate) {
            try {
                $fallbackFormatted = \Carbon\Carbon::parse($fallbackDate)->format('d.m.Y');
            } catch (\Throwable) {
                $fallbackFormatted = '';
            }
        }

        if (empty($selected)) {
            return $fallbackFormatted !== '' ? "Given on {$fallbackFormatted}" : '';
        }

        $grouped = [];
        $distinctDates = [];

        foreach ($selected as $vaccine) {
            $rawDate = $vaccineDates[$vaccine] ?? null;
            $formattedDate = '';
            if (is_string($rawDate) && trim($rawDate) !== '') {
                try {
                    $formattedDate = \Carbon\Carbon::parse($rawDate)->format('d.m.Y');
                } catch (\Throwable) {
                    $formattedDate = trim($rawDate);
                }
            } elseif ($fallbackFormatted !== '') {
                $formattedDate = $fallbackFormatted;
            }

            if ($formattedDate !== '') {
                $distinctDates[$formattedDate] = true;
            }
            $grouped[$formattedDate][] = $vaccine;
        }

        // Single date shared by every selected vaccine keeps the concise format
        if (count($distinctDates) === 1 && !isset($grouped[''])) {
            $onlyDate = array_key_first($distinctDates);
            return implode(', ', $selected) . " (Given on {$onlyDate})";
        }

        // No dates provided for any vaccine
        if (empty($distinctDates)) {
            return implode(', ', $selected);
        }

        // Mixed or distinct dates: group vaccines sharing each date
        $parts = [];
        foreach ($grouped as $dateLabel => $names) {
            $parts[] = $dateLabel !== ''
                ? implode(', ', $names) . " ({$dateLabel})"
                : implode(', ', $names);
        }

        return implode('; ', $parts);
    }
}
