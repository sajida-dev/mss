<?php

namespace Modules\Schools\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\ClassesSections\App\Models\ClassModel;
use Illuminate\Support\Facades\Storage;
use Modules\Admissions\Models\StudentEnrollment;
use Modules\Certificates\App\Models\Certificate;

class School extends Model
{
    use SoftDeletes;

    protected $table = 'schools';

    protected $fillable = [
        'name',
        'address',
        'contact',
        'logo',
        'main_image',
    ];

    protected $dates = ['deleted_at'];
    protected $appends = ['logo_url', 'main_image_url', 'initials'];

    /**
     * Get the URL to the school's logo.
     *
     * @return string
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return asset('storage/' . $this->logo);
        }
        // Return a default school logo
        return asset('storage/default-images/default-school-logo.png');
    }

    /**
     * Get the URL to the school's main image.
     *
     * @return string
     */
    public function getMainImageUrlAttribute()
    {
        if ($this->main_image && Storage::disk('public')->exists($this->main_image)) {
            return asset('storage/' . $this->main_image);
        }
        // Return a default school building image
        return asset('storage/default-images/default-school-building.png');
    }

    /**
     * Get the initials for the school name.
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

    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_schools', 'school_id', 'class_id')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
    public function studentEnrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }
}
