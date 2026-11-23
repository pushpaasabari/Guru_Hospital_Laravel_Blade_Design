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

