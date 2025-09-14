<?php

namespace Modules\Admissions\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\Fees\App\Models\Fee;
use Modules\Schools\App\Models\School;
use Illuminate\Support\Facades\Storage;
use Modules\Admissions\Models\StudentEnrollment;
use Modules\ClassesSections\app\Models\Section;
use Modules\ResultsPromotions\app\Models\ExamResult;

class Student extends Model
{
    use SoftDeletes;

    protected $table = 'students';

    protected $fillable = [
        'school_id',
        'class_id',
        'section_id',
        'nationality',
        'registration_number',
        'name',
        'b_form_number',
        'admission_date',
        'date_of_birth',
        'gender',
        'class_shift',
        'previous_school',
        'inclusive',
        'other_inclusive_type',
        'religion',
        'is_bricklin',
        'is_orphan',
        'is_qsc',
        'profile_photo_path',
        'father_name',
        'guardian_name',
        'father_cnic',
        'mother_cnic',
        'father_profession',
        'no_of_children',
        'job_type',
        'father_education',
        'mother_education',
        'mother_profession',
        'father_income',
        'mother_income',
        'household_income',
        'permanent_address',
        'phone_no',
        'mobile_no',
        'status',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
        'initials',
    ];

    /**
     * Get the URL to the student's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            return asset('storage/' . $this->profile_photo_path);
        }
        // Return a default student image
        return asset('storage/default-images/default-student.png');
    }

    /**
     * Get the initials for the student's name.
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
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function scopeAdmitted($query)
    {
        return $query->where('status', 'admitted');
    }


    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function fee()
    {
        return $this->hasOne(Fee::class)->where('type', 'admission');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function currentEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)->where('is_current', true);
    }

    public function results()
    {
        return $this->hasMany(ExamResult::class);
    }
}
