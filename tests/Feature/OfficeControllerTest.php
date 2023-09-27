<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OfficeControllerTest extends TestCase
{
    /**
     * @test
     */
    public function itListsOffices()
    {
        Office::factory(3)->create();

        $response = $this->get('/api/offices');

        $response->dump();

        $response->assertOk();
    }

    /**
     * @test
     */
    public function itFiltersByHostId()
    {
        Office::factory(3)->create();

        $host = User::factory()->create();

        $office = Office::factory()->for($host)->create();

        $response = $this->get("api/offices?host_id=" . $host->id);

        $response->dump();

        $response->assertOk();

        $this->assertCount(1, $response->json("data"));

        $this->assertEquals($office->id, $response->json("data")[0]["id"]);
    }

    /**
     * @test
     */
    public function itFiltersByUserId()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        Reservation::factory()->for($office)->for($user)->create();

        $response = $this->get("api/offices?user_id=" . $user->id);

        $this->assertCount(1, $response->json("data"));

        $this->assertEquals($office->id, $response->json("data")[0]["id"]);
    }

    /**
     *@test
     */
    public function itIncludesImagesTagsAndUser()
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create();

        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag);

        $office->images()->create(["path" => "image.jpg"]);

        $response = $this->get("api/offices?host_id=" . $user->id)->dump();

        $this->assertNotNull($response->json("data")[0]["tags"]);
        $this->assertNotNull($response->json("data")[0]["images"]);
        $this->assertNotNull($response->json("data")[0]["user"]);
    }

    /**
     * @test
     */
    public function itReturnsTheNumberOfActiveReservations()
    {
        $office = Office::factory()->create();

        Reservation::factory()->for($office)->create(["status" => Reservation::STATUS_CANCELLED]);
        Reservation::factory()->for($office)->create(["status" => Reservation::STATUS_ACTIVE]);

        $response = $this->get("api/offices");

        $this->assertEquals(1, $response->json("data")[0]["reservations_count"]);
    }

    /**
     * @test
     */

    public function itOrdersByDistanceWhenCoordinatesAreProvided()
    {
        //39.398513
        //30.015237

        $office1 = Office::factory()->create([
            "lat" => "39.400021",
            "lng" => "30.016182",
            "title" => "closest"
        ]);

        $office2 = Office::factory()->create([
            "lat" => "39.402283",
            "lng" => "30.019930",
            "title" => "furthest"
        ]);

        $response = $this->get("api/offices?lat=39.400021&lng=30.015237")->dump();

        $this->assertEquals("closest", $response->json("data")[0]["title"]);
    }

    /**
     * @test
     */

    public function itShowsTheOffice()
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create();

        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag);

        $office->images()->create(["path" => "image.jpg"]);

        Reservation::factory()->for($office)->create(["status" => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(["status" => Reservation::STATUS_CANCELLED]);

        $response = $this->get("api/offices/" . $office->id)->dump();

        $this->assertEquals($office->id, $response->json("data")["id"]);
        $this->assertEquals(1, $response->json("data")["reservations_count"]);
        $this->assertNotNull($response->json("data")["images"]);
        $this->assertNotNull($response->json("data")["tags"]);
        $this->assertNotNull($response->json("data")["user"]);
    }
}
