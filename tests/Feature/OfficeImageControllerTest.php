<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficeImageControllerTest extends TestCase
{
    /**
     * @test
     */
    public function test_example()
    {
        Storage::fake("public");

        $user = User::factory()->create();

        $office = Office::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson("api/offices/" . $office->id . "/images", [
            "image" => UploadedFile::fake()->image('test.jpg')
        ])->dump();

        $response->assertCreated();

        Storage::disk("public")->assertExists(
            $response->json("data.path")
        );
    }
}
