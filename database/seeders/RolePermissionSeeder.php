<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleUser = Role::create([
            "role" => Role::ROLE_USER
        ]);

        Permission::create(["name" => "office.create"]);
        Permission::create(["name" => "office.update"]);
        Permission::create(["name" => "office.delete"]);
        Permission::create(["name" => "reservations.show"]);
        Permission::create(["name" => "reservations.make"]);

        $permissions = Permission::all();

        $roleUser->permissions()->attach($permissions);
    }
}
