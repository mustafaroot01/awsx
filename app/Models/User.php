<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
        'branch_id',
        'api_token',
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
            'branch_id' => 'integer',
        ];
    }

    /**
     * Auto-assign الصلاحية الافتراضية to new users
     */
    protected static function boot(): void
    {
        parent::boot();

        static::created(function (User $user) {
            if ($user->roles()->count() === 0) {
                $default = Role::where('name', 'الصلاحية الافتراضية')->first();
                if ($default) {
                    $user->roles()->attach($default->id);
                }
            }
        });
    }

    /**
     * RBAC: Roles relationship
     */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * RBAC: Check if user has a specific permission via their roles
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Super Admins bypass all permission checks
        $superAdmins = ['mus2afa30@gmail.com', 'admin@admin.com', 'mus@mus.com', 'user@user.com'];
        if (in_array($this->email, $superAdmins)) return true;

        // Check if user belongs to a super-admin role
        if ($this->roles()->whereIn('name', ['إدارة النظام', 'مدير عام'])->exists()) return true;

        return $this->roles()->whereHas('permissions', function ($query) use ($permissionSlug) {
            $query->where('slug', $permissionSlug);
        })->exists();
    }

    /**
     * RBAC: Check if user belongs to a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * User's assigned branch (for branch managers)
     */
    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
