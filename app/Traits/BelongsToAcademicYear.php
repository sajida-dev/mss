<?php

namespace App\Traits;

use App\Models\AcademicYear;

trait BelongsToAcademicYear
{
    public static function bootBelongsToAcademicYear()
    {
        static::creating(function ($model) {
            if (empty($model->academic_year_id)) {
                $yearId = session('active_academic_year_id');

                if (!$yearId) {
                    $yearId = AcademicYear::where('status', 'active')->value('id');
                }

                $model->academic_year_id = $yearId;
            }
        });
    }
}
