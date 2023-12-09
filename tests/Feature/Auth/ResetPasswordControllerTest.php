<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use LazilyRefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function itResetsPassword()
    {
        $oldPassword = "123456";
        $newPassword = "1234567";

        $user = User::factory()->create([
            "password" => Hash::make($oldPassword)
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route("reset.password"), [
            "old_password" => $oldPassword,
            "password" => $newPassword,
            "password_confirmation" => $newPassword
        ]);

        $response->assertOk();

        $user = User::find($user->id);

        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    /**
     * @test
     */
    public function itDoesntResetPasswordForNonAuthenticatedUser()
    {
        $oldPassword = "123456";
        $newPassword = "1234567";

        User::factory()->create([
            "password" => Hash::make($oldPassword)
        ]);

        $response = $this->postJson(route("reset.password"), [
            "old_password" => $oldPassword,
            "password" => $newPassword,
            "password_confirmation" => $newPassword
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @test
     */
    public function itDoesntResetPasswordWithTrueOldPasswordButNonConfirmedNewPassword()
    {
        $oldPassword = "123456";
        $newPassword = "1234567";

        $user = User::factory()->create([
            "password" => Hash::make($oldPassword)
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route("reset.password"), [
            "old_password" => $oldPassword,
            "password" => $newPassword,
            "password_confirmation" => $newPassword . "2"
        ]);

        $response->assertUnprocessable();
    }

    /**
     * @test
     */

    public function itDoesntResetPasswordWithConfirmedNewPasswordButWrongOldPassword()
    {
        $oldPassword = "123456";
        $newPassword = "1234567";

        $user = User::factory()->create([
            "password" => Hash::make($oldPassword)
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route("reset.password"), [
            "old_password" => $oldPassword . "2",
            "password" => $newPassword,
            "password_confirmation" => $newPassword
        ]);

        $response->assertUnprocessable();
    }
}
