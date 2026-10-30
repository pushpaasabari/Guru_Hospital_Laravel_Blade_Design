<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicalStaff extends Model
{
    use HasFactory;

    protected $table = 'clinical_staff';

    public const ROLES = [
        'consultant_doctor' => 'Consultant Doctor',
        'surgeon' => 'Surgeon',
        'assisted_surgeon' => 'Assisted Surgeon',
        'anaesthetist' => 'Anaesthetist',
        'assisted_anaesthetist' => 'Assisted Anaesthetist',
        'staff_nurse' => 'Staff Nurse',
        'ot_technician' => 'OT Technician',
        'consultant_pediatrician' => 'Consultant Pediatrician',
    ];

    protected $fillable = [
        'name',
        'category',
        'roles',
        'qualification',
        'doctor_reg_no',
        'department',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'roles' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope for active clinical staff only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for clinical staff who perform a given role.
     */
    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->where(function ($q) use ($role) {
            $q->where('category', $role)
              ->orWhereJsonContains('roles', $role);
        });
    }

    /**
     * Check if this staff member performs a given role.
     */
    public function matchesRole(string $role): bool
    {
        if ($this->category === $role) {
            return true;
        }

        return is_array($this->roles) && in_array($role, $this->roles, true);
    }

    /**
     * Get array of human readable role labels.
     */
    public function getRoleLabelsAttribute(): array
    {
        $allRoles = $this->roles ?: [$this->category];
        if (!in_array($this->category, $allRoles, true)) {
            $allRoles[] = $this->category;
        }

        $labels = [];
        foreach ($allRoles as $r) {
            if (isset(self::ROLES[$r])) {
                $labels[] = self::ROLES[$r];
            }
        }

        return array_unique($labels);
    }
}

