<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficeImageControllerTest extends TestCase
{
    Use LazilyRefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function itUploadsAnImageAndStoresItUnderTheOffice()
    {
        Storage::fake();

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->postJson("api/offices/{$office->id}/images", [
            "image" => UploadedFile::fake()->image('image.jpg')
        ]);

        $response->assertCreated();

        $response->assertStatus(Response::HTTP_CREATED);

        Storage::assertExists(
            $response->json("data.path")
        );
    }


     /**
     * @test
     */
    public function itDeletesAnImage()
    {
        Storage::put("office_image.jpg", "empty");

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $image1 = $office->images()->create([
            "path" => "office_image.jpg"
        ]);

        $image2 = $office->images()->create([
            "path" => $this->faker->word  . ".jpg"
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson("api/offices/{$office->id}/images/{$image1->id}");

        $response->assertOk();

        $this->assertModelMissing($image1);

        Storage::assertMissing("office_image.jpg");
    }

     /**
     * @test
     */
     public function itDoesntDeleteTheOnlyImage()
     {
         $user = User::factory()->create();
         $office = Office::factory()->for($user)->create();

         $image = $office->images()->create([
             "path" => $this->faker->word . ".jpg"
         ]);

         $this->actingAs($user);

         $response = $this->deleteJson("api/offices/{$office->id}/images/{$image->id}");

         $response->assertUnprocessable()
             ->assertJsonValidationErrors(["error" => "Cannot delete the only image."]);
     }

    /**
     * @test
     */
    public function itDoesntDeleteTheFeaturedImage()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $image1 = $office->images()->create([
            "path" => $this->faker->word . ".jpg"
        ]);

        $image2 = $office->images()->create([
            "path" => $this->faker->word . ".jpg"
        ]);

        $office->update(["featured_image_id" => $image1->id]);

        $this->actingAs($user);

        $response = $this->deleteJson("api/offices/{$office->id}/images/{$image1->id}");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(["error" => "Cannot delete the featured image."]);
    }

     /**
     * @test
     */
     public function itDoesntDeleteTheImageThatBelongsToAnotherResource()
     {
         $user = User::factory()->create();

         $office = Office::factory()->for($user)->create();
         $office2 = Office::factory()->for($user)->create();

         $image2 = $office2->images()->create([
             "path" => $this->faker->word . ".jpg",
         ]);

         $this->actingAs($user);

         $response = $this->deleteJson("api/offices/{$office->id}/images/{$image2->id}");

         $response->assertNotFound();
     }
}
