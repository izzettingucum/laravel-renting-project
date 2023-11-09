<?php

namespace Database\Factories;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            "is_admin" => false
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function withRole($role)
    {
        return $this->afterCreating(function (User $user) use ($role) {
            if (! Role::where("role", $role)->exists()) {
                $role = Role::create(["role" => $role]);
                $permissions = Permission::factory()->create();
                $role->permissions()->attach($permissions);
            }
            else {
                $role = Role::where("role", $role)->first();
            }

            $user->userRole()->create([
                "role_id" => $role->id
            ]);
        });
    }

}
