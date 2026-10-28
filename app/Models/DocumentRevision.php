<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRevision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admission_id',
        'revision_number',
        'snapshot_data',
        'pdf_storage_path',
        'approved_by',
        'approved_at',
        'created_at',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

