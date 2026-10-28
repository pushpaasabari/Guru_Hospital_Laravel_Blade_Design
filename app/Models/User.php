<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'doctor_reg_no',
        'department',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'administrator';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isNursing(): bool
    {
        return $this->role === 'nursing_staff';
    }

    public function isOtStaff(): bool
    {
        return $this->role === 'ot_staff';
    }

    public function isMrdStaff(): bool
    {
        return $this->role === 'mrd_staff';
    }

    public function isAuditor(): bool
    {
        return $this->role === 'auditor';
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }
        return $this->role === $roles;
    }

    public function canApproveDocuments(): bool
    {
        return $this->isAdmin() || $this->isDoctor();
    }

    public function canGeneratePdfs(): bool
    {
        return $this->isAdmin() || $this->isDoctor() || $this->isMrdStaff() || $this->isNursing();
    }

    public function canEditAdmissions(): bool
    {
        return !$this->isAuditor();
    }

    public function canViewAuditLogs(): bool
    {
        return $this->isAdmin() || $this->isAuditor();
    }
}
