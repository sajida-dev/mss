<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Auth\MustVerifyEmail as AuthMustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Modules\Schools\App\Models\School;
use Modules\Teachers\Models\Teacher;
use Illuminate\Support\Facades\Storage;
use Modules\ResultsPromotions\app\Models\ExamResult;

/**
 * @method bool hasRole(string|array $roles, string|null $guard = null)
 * @method bool hasPermissionTo(string $permission, string|null $guard = null)
 * @method \Spatie\Permission\Models\Role[] getRoleNames()
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, AuthMustVerifyEmail, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'profile_photo_path',
        'phone_number',
        'last_school_id',
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
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            return asset('storage/' . $this->profile_photo_path);
        }
        // Return a default image if no profile photo is set
        return asset('storage/default-profile.png');
    }

    /**
     * Get the initials for the user's name.
     *
     * @return string
     */
    public function getInitialsAttribute()
    {
        return collect(explode(' ', $this->name))
            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
            ->take(2)
            ->join('');
    }



    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
        'initials',
    ];

    public function permissionMap(): array
    {
        return $this->getAllPermissions()
            ->pluck('name')
            ->mapWithKeys(fn($p) => [$p => true])
            ->toArray();
    }


    protected $guard_name = 'web';
    public function getDefaultGuardName(): string
    {
        return 'web';
    }

    public function userPassword()
    {
        return $this->hasOne(UserPassword::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }
    public function schools()
    {
        return $this->belongsToMany(
            \Modules\Schools\app\Models\School::class,
            'model_has_roles',
            'model_id',    // FK to user
            'school_id',     // FK to school/team
        )->where('model_type', self::class)->distinct();
    }
    // Add the schools relationship if not present
    // public function schools()
    // {
    //     return $this->belongsToMany(School::class);
    // }
    // Optionally, add an accessor/mutator for last_school_id if you add it to the DB
    public function getLastSchoolIdAttribute()
    {
        return $this->attributes['last_school_id'] ?? null;
    }
    public function setLastSchoolIdAttribute($value)
    {
        $this->attributes['last_school_id'] = $value;
    }
    /**
     * Get the current team/school ID for Spatie permission package
     *
     * This tells Spatie which team (school) context to use when checking roles/permissions.
     */
    public function getPermissionsTeamId(): ?int
    {
        // You can return the active school from session or user property
        return session('active_school_id') ?? $this->last_school_id ?? null;
    }

    public function result()
    {
        return $this->hasMany(ExamResult::class, 'marked_by');
    }
}
