<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Traits\LogsActivity;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'is_employee',
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
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'is_employee'       => 'boolean',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * The roles that belong to this user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Tasks created by this user.
     */
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Tasks assigned to this user.
     */
    public function assignedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_assignees')->withTimestamps();
    }

    // -----------------------------------------------------------------------
    // Employee Performance relationships
    // -----------------------------------------------------------------------

    public function employeeProfile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function performanceTaskTemplates()
    {
        return $this->hasMany(PerformanceTaskTemplate::class);
    }

    public function employeeAttendances()
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function performanceDailyEntries()
    {
        return $this->hasMany(PerformanceDailyEntry::class);
    }

    public function performanceMonthlyReports()
    {
        return $this->hasMany(PerformanceMonthlyReport::class);
    }

    /**
     * Check if this user is marked to work as an employee.
     */
    public function isEmployee(): bool
    {
        return (bool) $this->is_employee;
    }

    /**
     * Scope a query to only include users marked as employees.
     */
    public function scopeEmployees($query)
    {
        return $query->where('is_employee', true);
    }


    public function receivesBroadcastNotificationsOn(): string
    {
        return 'users.' . $this->id;
    }


    // -----------------------------------------------------------------------
    // Permission helpers (cached per request)
    // -----------------------------------------------------------------------

    /** @var array<string>|null */
    protected ?array $permissionsCache = null;

    /**
     * Collect all permission names the user has via their roles.
     *
     * @return array<string>
     */
    public function getAllPermissions(): array
    {
        if ($this->permissionsCache !== null) {
            return $this->permissionsCache;
        }

        $roles = $this->roles()->with(['permissions', 'permissionGroups.permissions'])->get();

        $directPermissions = $roles->flatMap(fn (Role $role) => $role->permissions->pluck('name'));
        $groupPermissions  = $roles->flatMap(fn (Role $role) => $role->permissionGroups->flatMap(fn ($group) => $group->permissions->pluck('name')));

        $this->permissionsCache = $directPermissions->concat($groupPermissions)
            ->unique()
            ->values()
            ->all();

        return $this->permissionsCache;
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getAllPermissions(), true);
    }

    /**
     * Check if the user has a specific role by name.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    /**
     * Check if the user is a Super Admin (bypasses all permission checks).
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching([$role->id]);
        $this->permissionsCache = null;
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role->id);
        $this->permissionsCache = null;
    }
}
