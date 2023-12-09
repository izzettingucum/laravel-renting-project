<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\Auth\ForgotPasswordNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use LazilyRefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function itSendsRecoveryCodeToUserEmail()
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->postJson(route("send.recovery.code"), [
            "email" => $user->email
        ]);

        Notification::assertSentTo($user, ForgotPasswordNotification::class);

        $this->assertDatabaseHas("forgot_password_mails", [
            "user_id" => $user->id
        ]);

        $response->assertOk();
    }

    /**
     * @test
     */
    public function itDoesntSendRecoveryCodeToCurrentLoggedInUser()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson(route("send.recovery.code"), [
            "email" => $user->email
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @test
     */
    public function itDoesntSendRecoveryCodeToNonExistingUserEmail()
    {
        $response = $this->postJson(route("send.recovery.code"), [
            "email" => $this->faker->email
        ]);

        $response->assertUnprocessable();
    }

    /**
     * @test
     */
    public function itApprovesRecoveryCodeIfRecoveryCodeIsTrue()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson(route("send.recovery.code"), [
            "email" => $user->email
        ]);

        $recoveryCode = $user->forgotPasswordMail()->first();

        $response = $this->postJson(route("check.recovery.code"), [
            "code" => $recoveryCode->code,
            "email" => $user->email
        ]);

        $response->assertOk();
    }

    /**
     * @test
     */
    public function itDoesntApproveRecoveryCodeIfRecoveryCodeIsNotTrue()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson(route("send.recovery.code"), [
            "email" => $user->email
        ]);

        $response = $this->postJson(route("check.recovery.code"), [
            "code" => $this->faker->numberBetween(0, 1000),
            "email" => $user->email
        ]);

        $response->assertUnprocessable();
    }

    /**
     * @test
     */

    public function itDoesntApproveRecoveryCodeWithTrueCodeButNonExistingEmail()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson(route("send.recovery.code"), [
            "email" => $user->email
        ]);

        $recoveryCode = $user->forgotPasswordMail()->first();

        $response = $this->postJson(route("check.recovery.code"), [
            "code" => $recoveryCode->code,
            "email" => $this->faker->email
        ]);

        $response->assertUnprocessable();
    }

    /**
     * @test
     */

    public function itDoesntApproveRecoveryCodeWithTrueCodeButEmailThatBelongsToAnotherUser()
    {
        Notification::fake();

        $user = User::factory()->create();

        $anotherUser = User::factory()->create();

        $this->postJson(route("send.recovery.code"), [
            "email" => $user->email
        ]);

        $this->postJson(route("send.recovery.code"), [
            "email" => $anotherUser->email
        ]);

        $recoveryCode = $user->forgotPasswordMail()->first();

        $response = $this->postJson(route("check.recovery.code"), [
            "code" => $recoveryCode->code,
            "email" => $anotherUser->email
        ]);

        $response->assertUnprocessable();
    }

    /**
     * @test
     */
    public function itResetsPassword()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson(route("send.recovery.code"), [
            "email" => $user->email
        ]);

        $recoveryCode = $user->forgotPasswordMail()->first();

        $checkCodeResponse = $this->postJson(route("check.recovery.code"), [
            "code" => $recoveryCode->code,
            "email" => $user->email
        ]);

        $password = "12345678";

        $resetPasswordResponse = $this->postJson(route("forgot.reset.password"), [
            "email" => $user->email,
            "remember_token" => $checkCodeResponse->json("remember_token"),
            "password" => $password,
            "password_confirmation" => $password
        ]);

        $resetPasswordResponse->assertOk();

        $user = User::find($user->id);

        $this->assertTrue(Hash::check($password, $user->password));
    }

    /**
     * @test
     */
    public function itDoesntResetPasswordWithTrueRememberTokenAndNonExistingEmail()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson(route("send.recovery.code"), [
            "email" => $user->email
        ]);

        $recoveryCode = $user->forgotPasswordMail()->first();

        $checkCodeResponse = $this->postJson(route("check.recovery.code"), [
            "code" => $recoveryCode->code,
            "email" => $user->email
        ]);

        $password = "12345678";

        $resetPasswordResponse = $this->postJson(route("forgot.reset.password"), [
            "email" => $this->faker->email,
            "remember_token" => $checkCodeResponse->json("remember_token"),
            "password" => $password,
            "password_confirmation" => $password
        ]);

        $resetPasswordResponse->assertUnprocessable();
    }

    /**
     * @test
     */
    public function itDoesntResetPasswordWithTrueRememberTokenAndEmailThatBelongsToAnotherUser()
    {
        Notification::fake();

        $user = User::factory()->create();

        $anotherUser = User::factory()->create();

        $anotherUser->forgotPasswordMail()->create([
            "code" => rand(100000, 1000000),
            "remember_token" => Str::random(8),
            "expired_at" => now()->addMinute(5)
        ]);

        $this->postJson(route("send.recovery.code"), [
            "email" => $user->email
        ]);

        $recoveryCode = $user->forgotPasswordMail()->first();

        $checkCodeResponse = $this->postJson(route("check.recovery.code"), [
            "code" => $recoveryCode->code,
            "email" => $user->email
        ]);

        $password = "12345678";

        $resetPasswordResponse = $this->postJson(route("forgot.reset.password"), [
            "email" => $anotherUser->email,
            "remember_token" => $checkCodeResponse->json("remember_token"),
            "password" => $password,
            "password_confirmation" => $password
        ]);

        $resetPasswordResponse->assertUnprocessable();
    }

    /**
     * @test
     */
    public function itDoesntResetPasswordWithTrueEmailAndWrongRememberToken()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson(route("send.recovery.code"), [
            "email" => $user->email
        ]);

        $recoveryCode = $user->forgotPasswordMail()->first();

        $this->postJson(route("check.recovery.code"), [
            "code" => $recoveryCode->code,
            "email" => $user->email
        ]);

        $password = "12345678";

        $resetPasswordResponse = $this->postJson(route("forgot.reset.password"), [
            "email" => $user->email,
            "remember_token" => $this->faker->password(6, 8),
            "password" => $password,
            "password_confirmation" => $password
        ]);

        $resetPasswordResponse->assertUnprocessable();
    }
}
