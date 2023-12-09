<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()->create([
            "email" => "izzettin_43@hotmail.com",
            "email_verified_at" => now()
        ]);

        $role = Role::where("role", Role::ROLE_USER)->first();

        $user->userRole()->create([
            "role_id" => $role->id
        ]);
    }
}
