<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerificationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function itDoesntVerifyTheUserWhoHasAlreadyVerified()
    {
        $user = User::factory()->create([
            "email_verified_at" => now()->subDay(2)
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route("verification.send"));

        $response->assertUnprocessable();
    }
}
