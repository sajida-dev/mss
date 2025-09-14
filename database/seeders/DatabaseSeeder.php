<?php

namespace Database\Seeders;

use App\Models\User;
use Modules\Schools\App\Models\School;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles, permissions, and assignments
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Classes and sections (must come after schools)
        $this->call(\Modules\ClassesSections\Database\Seeders\ClassSeeder::class);
        $this->call(\Modules\ClassesSections\Database\Seeders\SectionSeeder::class);
    }
}
