<?php

namespace Modules\ClassesSections\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ClassesSections\app\Models\ClassSchool;
use Modules\ClassesSections\app\Models\SchoolClass;
use Modules\ClassesSections\app\Models\Section;

class SectionSeeder extends Seeder
{
    public function run()
    {
        $sections = range('A', 'Z');

        foreach ($sections as $section) {
            Section::create([
                'name' => $section,
            ]);
        }
    }
}
