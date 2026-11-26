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
        'birth_vaccines', 'vaccine_date',
    ];

    protected $casts = [
        'birth_at' => 'datetime',
        'birth_weight_kg' => 'float',
        'discharge_weight_kg' => 'float',
        'birth_vaccines' => 'array',
        'vaccine_date' => 'date:Y-m-d',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }
}
