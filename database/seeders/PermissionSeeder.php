<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create(["name" => "office.create"]);
        Permission::create(["name" => "office.update"]);
        Permission::create(["name" => "office.delete"]);
        Permission::create(["name" => "reservations.show"]);
        Permission::create(["name" => "reservations.make"]);
    }
}
