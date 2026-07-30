<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Roles for the new Filament CMS (/cms) authorization model. The legacy
 * /admin panel keeps using the `type == 1` check independently until
 * cutover -- these roles do not affect it.
 */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('editor', 'web');
    }
}
