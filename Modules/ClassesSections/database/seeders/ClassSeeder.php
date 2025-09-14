<?php

namespace Modules\ClassesSections\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ClassesSections\app\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;

class ClassSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 12; $i++) {
            ClassModel::create(['name' => "Class $i"]);
        }
    }
}
