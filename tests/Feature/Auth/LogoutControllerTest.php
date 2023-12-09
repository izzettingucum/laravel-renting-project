<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function itDeletesTokenSuccessfully()
    {
        $user = User::factory()->create();

        $token = $user->createToken("test-token", ["*"])->plainTextToken;

        $response = $this->postJson(route("logout"), [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertSuccessful();

        $this->assertNull($user->currentAccessToken());
    }
}
