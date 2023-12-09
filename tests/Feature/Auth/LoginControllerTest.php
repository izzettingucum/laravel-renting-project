<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\TwoFactorCode;
use App\Models\User;
use App\Notifications\Auth\TwoFactorNotification;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use LazilyRefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function itDoesntLoginWithCurrentLoggedInUser()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson(route("login"));

        $response->assertUnauthorized();
    }

    /**
     * @test
     */
    public function itDoesntLoginWithBadCredentials()
    {
        $response = $this->postJson(route("login"), [
            "email" => $this->faker->email,
            "password" => $this->faker->password
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function itDoesntLoginWithoutTwoFactor()
    {
        $user = User::factory()->create([
            "two_factor_auth" => true,
            "email" => $this->faker->email,
            "password" => Hash::make("123456")
        ]);

        $response = $this->postJson(route("login"), [
            "email" => $user->email,
            "password" => "123456"
        ]);

        $response->assertOk();

        $this->assertGuest();
    }

    /**
     * @test
     */
    public function itSendsTwoFactorNotification()
    {
        Notification::fake();

        $user = User::factory()->create([
            "two_factor_auth" => true,
            "email" => $this->faker->email,
            "password" => Hash::make("123456")
        ]);

        $response = $this->postJson(route("login"), [
            "email" => $user->email,
            "password" => "123456"
        ]);

        $response->assertOk();

        Notification::assertSentTo($user, TwoFactorNotification::class);
    }

    /**
     * @test
     */
    public function itAuthenticatesUserWithTwoFactorCode()
    {
        Notification::fake();

        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create([
            "two_factor_auth" => true,
            "email" => $this->faker->email,
            "password" => Hash::make("123456")
        ]);

        $role = Role::where("role", Role::ROLE_USER)->first();

        $user->userRole()->create([
            "role_id" => $role->id
        ]);

        $this->postJson(route("login"), [
            "email" => $user->email,
            "password" => "123456"
        ]);

        $twoFactorCode = TwoFactorCode::query()
            ->where("user_id", $user->id)
            ->latest("id")
            ->first();

        $loginRequest = $this->postJson(route("login.twoFactor"), [
            "email" => $user->email,
            "code" => $twoFactorCode->code
        ]);

        $loginRequest->assertOk();

        $this->assertNotNull($loginRequest->json("user")["token"]);
    }

    /**
     * @test
     */

    public function itDoesntAuthenticateWithTwoFactorByWrongTwoFactorCode()
    {
        Notification::fake();

        $user = User::factory()->create([
            "two_factor_auth" => true,
            "email" => $this->faker->email,
            "password" => Hash::make("123456")
        ]);

        $this->postJson(route("login"), [
            "email" => $user->email,
            "password" => "123456"
        ]);

        $loginRequest = $this->postJson(route("login.twoFactor"), [
            "email" => $user->email,
            "code" => $this->faker->numberBetween(0, 1000)
        ]);

        $loginRequest->assertUnprocessable();
    }

    /**
     * @test
     */
    public function itDoesntAuthenticateWithTwoFactorByExpiredTwoFactorCode()
    {
        Notification::fake();

        $user = User::factory()->create([
            "two_factor_auth" => true,
            "email" => $this->faker->email,
            "password" => Hash::make("123456")
        ]);

        $this->postJson(route("login"), [
            "email" => $user->email,
            "password" => "123456"
        ]);

        $twoFactorCode = TwoFactorCode::query()
            ->where("user_id", $user->id)
            ->latest("id")
            ->first();

        Carbon::setTestNow(Carbon::now()->addHour());

        $loginRequest = $this->postJson(route("login.twoFactor"), [
            "email" => $user->email,
            "code" => $twoFactorCode->code
        ]);

        $loginRequest->assertUnprocessable();

        Carbon::setTestNow();
    }
}
