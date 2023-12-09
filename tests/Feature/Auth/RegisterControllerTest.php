<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RolePermissionSeeder;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use WithFaker, LazilyRefreshDatabase;

    /**
     * @test
     */
    public function itRegistersUser()
    {
        Notification::fake();

        $this->seed(RolePermissionSeeder::class);

        $response = $this->postJson(route("register"), [
            "name" => $this->faker->name,
            "email" => "example@hotmail.com",
            "password" => "123456",
            "password_confirmation" => "123456"
        ]);

        $user = User::where("email", "example@hotmail.com")->first();

        Notification::assertSentTo($user, VerifyEmail::class);

        $response->assertOk();
    }

    /**
     * @test
     */
    public function itDoesntRegisterWithLoggedInUser()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson(route("register"), [
                "name" => $this->faker->name,
                "email" => "izzettin_43@hotmail.com",
                "password" => "123456",
                "password_confirmation" => "123456"
            ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function itDoesntRegisterWithConflictingEmail()
    {
        $user = User::factory()->create([
            "email" => "deneme@gmail.com"
        ]);

        $response = $this->postJson(route("register"), [
            "name" => $this->faker->name,
            "email" => $user->email,
            "password" => "123456",
            "password_confirmation" => "123456"
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
