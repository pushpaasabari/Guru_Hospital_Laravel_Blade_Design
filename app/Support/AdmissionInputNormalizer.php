<?php

namespace App\Support;

class AdmissionInputNormalizer
{
    /**
     * Admission fields that must always be stored in uppercase.
     *
     * @var array<int, string>
     */
    public const UPPERCASE_FIELDS = [
        'patient_name',
        'attender_name',
        'address',
        'address_line1',
        'address_line2',
        'address_line3',
        'vhn_name',
        'phc',
        'consultant',
        'surgeon',
        'assisted_surgeon',
        'pediatrician_name',
        'anaesthetist',
        'assisted_anaesthetist',
        'assistant',
        'assisted_by',
        'staff_nurse',
        'ot_technician',
    ];

    /**
     * Join an array or delimited string of clinical selections cleanly, preserving commas inside degrees.
     *
     * @param  string|array<int, string>|null  $value
     */
    public static function joinMulti(string|array|null $value, bool $uppercase = true): ?string
    {
        if ($value === null) {
            return null;
        }

        $items = is_array($value)
            ? $value
            : (preg_split('/[;\r\n|]+/', (string) $value) ?: []);

        $seen = [];
        $result = [];

        foreach ($items as $item) {
            $cleaned = trim((string) $item);
            if ($cleaned === '') {
                continue;
            }
            if ($uppercase) {
                $cleaned = mb_strtoupper($cleaned, 'UTF-8');
            }
            $key = mb_strtolower($cleaned, 'UTF-8');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $result[] = $cleaned;
        }

        return $result === [] ? null : implode('; ', $result);
    }

    /**
     * Convert a nullable clinical text value to uppercase, returning null when blank.
     */
    public static function toUpper(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        return mb_strtoupper($trimmed, 'UTF-8');
    }

    /**
     * Strip all non-digit characters from a phone / contact string, returning null when blank.
     */
    public static function digitsOnly(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        return ($digits === null || $digits === '') ? null : $digits;
    }
}

