<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

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
        ];
    }

    /**
     * Role-based query scopes
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeDoctors($query)
    {
        return $query->where('role', 'doctor');
    }

    public function scopeNurses($query)
    {
        return $query->where('role', 'nurse');
    }

    public function scopeStaff($query)
    {
        return $query->where('role', 'staff');
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is doctor
     */
    public function isDoctor()
    {
        return $this->hasRole('doctor');
    }

    /**
     * Check if user is nurse
     */
    public function isNurse()
    {
        return $this->hasRole('nurse');
    }

    /**
     * Check if user is staff
     */
    public function isStaff()
    {
        return $this->hasRole('staff');
    }

    /**
     * Get role label
     */
    public function getRoleLabelAttribute()
    {
        $roles = [
            'admin' => 'Administrator',
            'doctor' => 'Dokter',
            'nurse' => 'Perawat',
            'staff' => 'Staff'
        ];

        return $roles[$this->role] ?? 'Unknown';
    }
}
