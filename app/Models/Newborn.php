<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Newborn extends Model
{
    protected $fillable = [
        'admission_id', 'sex', 'birth_at', 'birth_weight_kg',
        'apgar_1_min', 'apgar_5_min', 'neonatal_care', 'notes'
    ];

    protected $casts = [
        'birth_at' => 'datetime',
        'birth_weight_kg' => 'float',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }
}
