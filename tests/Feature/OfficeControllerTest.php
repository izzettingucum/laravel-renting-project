<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\OfficePendingApproval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OfficeControllerTest extends TestCase
{
    /**
     * @test
     */
    public function itListsOffices()
    {
        Office::factory(3)->create();

        $response = $this->get('/api/offices')->dump();

        $response->assertOk();
    }

    /**
     * @test
     */
    public function itFiltersByUserId()
    {
        Office::factory(3)->create();

        $host = User::factory()->create();
        $office = Office::factory()->for($host)->create();

        $response = $this->get("api/offices?user_id=" . $host->id);

        $response->dump();

        $response->assertOk();

        $this->assertCount(1, $response->json("data"));

        $this->assertEquals($office->id, $response->json("data")[0]["id"]);
    }

     /**
     * @test
     */
     public function itListsOfficesIncludingHiddenAndUnapprovedIfFilteringForTheCurrentLoggedInUser()
     {
         $user = User::factory()->create();

         $office1 = Office::factory()->for($user)->create([
             "approval_status" => Office::APPROVAL_APPROVED,
             "hidden" => false
         ]);

         $office2 = Office::factory()->for($user)->create([
             "approval_status" => Office::APPROVAL_PENDING,
             "hidden" => false
         ]);

         $office3 = Office::factory()->for($user)->create([
             "approval_status" => Office::APPROVAL_PENDING,
             "hidden" => true
         ]);

         $this->actingAs($user);

         $response = $this->get("api/offices?user_id=" . $user->id)->dump();

         $response->assertOk()
             ->assertJsonCount(3,"data");

     }

    /**
     * @test
     */
    public function itFiltersByVisitorId()
    {
        $user = User::factory()->create();
        $office = Office::factory()->create();

        Reservation::factory()->for($office)->for($user)->create();

        $response = $this->get("api/offices?visitor_id=" . $user->id);

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

    /**
     * @test
     */

    public function itCreatesAnOffice()
    {
        Notification::fake();

        $admin = User::find(1);
        $user = User::factory()->createQuietly();

        $tag = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson("api/offices", [
            "title" => "Deneme Başlığı",
            "description" => "Deneme Açıklaması",
            "lat" => "39.400021",
            "lng" => "30.016182",
            "address_line1" => "address",
            "price_per_day" => 10000,
            "monthly_discount" => 25,
            "tags" => [$tag->id, $tag2->id]
        ])->dump();

        $response->assertCreated()
            ->assertJsonPath("data.title", "Deneme Başlığı")
            ->assertJsonPath("data.user.id", $user->id)
            ->assertJsonCount(2, "data.tags");

        $this->assertDatabaseHas("offices", [
            "title" => "Deneme Başlığı"
        ]);

        Notification::assertSentTo($admin, OfficePendingApproval::class);
    }

     /**
     * @test
     */

     public function itUpdatesAnOffice()
     {
         $user = User::factory()->create();

         $tags = Tag::factory(2)->create();
         $anotherTag = Tag::factory()->create();

         $office = Office::factory()->for($user)->create();
         $office->tags()->attach($tags);

         $this->actingAs($user);

         $title = "Updated Title";

         $response = $this->patchJson("api/offices/" . $office->id, [
             "title" => $title,
             "tags" => [$tags[0]->id, $anotherTag->id]
         ])->dump();

         $response->assertOk()
         ->assertJsonCount(2, "data.tags")
         ->assertJsonPath("data.tags.0.id", $tags[0]->id)
         ->assertJsonPath("data.title", $title);
     }

     /**
     * @test
     */
    public function itDoesntUpdateOfficeThatDoesntBelongsToUser()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $this->actingAs($anotherUser);

        $response = $this->patchJson("api/offices/" . $office->id, [
            "title" => "unauthorized"
        ])->dump();

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function itMarksTheOfficeAsPendingIfDirty()
    {
        $admin = User::factory()->create(["is_admin" => true]);

        Notification::fake();

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $this->patchJson("api/offices/" . $office->id, [
            "price_per_day" => 100
        ])->dump();

        $this->assertDatabaseHas("offices", [
            "id" => $office->id,
            "approval_status" => Office::APPROVAL_PENDING
        ]);

        Notification::assertSentTo($admin, OfficePendingApproval::class);
    }

     /**
     * @test
     */
     public function itCanDeleteOffices()
     {
         $user = User::factory()->create();
         $office = Office::factory()->for($user)->create();

         $this->actingAs($user);

         $response = $this->deleteJson("api/offices/" . $office->id);

         $response->assertOk();

         $this->assertSoftDeleted($office);
     }

    /**
     * @test
     */

    public function itCannotDeleteAnOfficeThatHasReservations()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        Reservation::factory()->for($office)->create(["status" => Reservation::STATUS_ACTIVE]);

        $this->actingAs($user);

        $response = $this->deleteJson("api/offices/" . $office->id)->dump();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas("offices", [
            "id" => $office->id,
            "deleted_at" => null
        ]);
    }
}
