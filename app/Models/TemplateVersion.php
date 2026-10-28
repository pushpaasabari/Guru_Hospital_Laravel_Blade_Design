<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateVersion extends Model
{
    protected $fillable = [
        'template_code',
        'version_tag',
        'title',
        'description',
        'is_approved_by_hospital',
        'approved_by_name',
        'effective_date',
        'updated_by',
    ];

    protected $casts = [
        'is_approved_by_hospital' => 'boolean',
        'effective_date' => 'date',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

