<?php

namespace Offices;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\Offices\OfficeCreatedNotification;
use App\Notifications\Offices\OfficeUpdatedNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficeControllerTest extends TestCase
{
    use LazilyRefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function itListsOffices()
    {
        Office::factory(3)->create();

        $response = $this->get(route("offices.list"));

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

        $response = $this->get(route("offices.list") . "?" . http_build_query([
            "user_id" => $host->id
        ]));

        $response->assertOk();

        $this->assertCount(1, $response->json("data"));

        $this->assertEquals($office->id, $response->json("data")[0]["id"]);
    }

    /**
     * @test
     */
    public function itFiltersByTags()
    {
        $officeCount = 2;
        $tagCount = 4;

        $tags = Tag::factory($tagCount)->create();

        $office = Office::factory($officeCount)->hasAttached($tags)->create();
        Office::factory()->hasAttached($tags->first());

        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->withRole(ROLE::ROLE_USER)->create();

        $this->actingAs($user);

        $response = $this->getJson(route("offices.list") . "?" . http_build_query([
                "tags" => $tags->pluck("name")->toArray()
            ]));

        $response->assertOk()
            ->assertJsonPath("data.0.id", $office->last()->id)
            ->assertJsonCount($officeCount, "data")
            ->assertJsonCount($tagCount, "data.0.tags");

    }

    /**
     * @test
     */
    public function itListsOfficesIncludingHiddenAndUnapprovedIfFilteringForTheCurrentLoggedInUser()
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->withRole(ROLE::ROLE_USER)->create();

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

        $response = $this->get(route("offices.list") . "?" . http_build_query([
                "user_id" => $user->id
            ]));

        $response->assertOk()
            ->assertJsonCount(3, "data");

    }

    /**
     * @test
     */
    public function itFiltersByVisitorId()
    {
        $user = User::factory()->create();
        $office = Office::factory()->create();

        Reservation::factory()->for($office)->for($user)->create();

        $response = $this->get(route("offices.list") . "?" . http_build_query([
                "visitor_id" => $user->id
            ]));

        $this->assertCount(1, $response->json("data"));

        $this->assertEquals($office->id, $response->json("data")[0]["id"]);
    }

    /**
     * @test
     */
    public function itIncludesImagesTagsAndUser()
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create();

        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag);

        $office->images()->create(["path" => $this->faker->word . ".jpg"]);

        $response = $this->get(route("offices.list") . "?" . http_build_query([
                "host_id" => $user->id
            ]));

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

        $response = $this->get((route("offices.list")));

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

        $response = $this->getJson(route("offices.list") . "?" . http_build_query([
                "lat" => 39.400021,
                "lng" => 30.015237
            ]));

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
        $office->images()->create(["path" => $this->faker->word . ".jpg"]);

        Reservation::factory()->for($office)->create(["status" => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(["status" => Reservation::STATUS_CANCELLED]);

        $response = $this->get(route("offices.show", $office->id));

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

        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->withRole(ROLE::ROLE_USER)->create();
        $admin = User::factory()->withRole(Role::ROLE_ADMIN)->create();

        $tag = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson(route("offices.create"), [
            "title" => "Deneme Başlığı",
            "description" => "Deneme Açıklaması",
            "user_id" => $user->id,
            "lat" => "39.400021",
            "lng" => "30.016182",
            "address_line1" => "address",
            "price_per_day" => 10000,
            "monthly_discount" => 25,
            "tags" => [$tag->id, $tag2->id]
        ]);

        $response->assertCreated()
            ->assertJsonPath("data.title", "Deneme Başlığı")
            ->assertJsonPath("data.user.id", $user->id)
            ->assertJsonCount(2, "data.tags");

        $this->assertDatabaseHas("offices", [
            "title" => "Deneme Başlığı"
        ]);

        Notification::assertSentTo($admin, OfficeCreatedNotification::class);
    }

    /**
     * @test
     */

    public function itUpdatesAnOffice()
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->withRole(ROLE::ROLE_USER)->create();

        $tags = Tag::factory(2)->create();
        $anotherTag = Tag::factory()->create();

        $office = Office::factory()->for($user)->create();
        $office->tags()->attach($tags);

        $this->actingAs($user);

        $title = "Updated Title";

        $response = $this->patchJson(route("offices.update", $office->id), [
            "title" => $title,
            "tags" => [$tags[0]->id, $anotherTag->id]
        ]);

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
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->withRole(ROLE::ROLE_USER)->create();

        $anotherUser = User::factory()->withRole(ROLE::ROLE_USER)->create();

        $office = Office::factory()->for($user)->create();

        $this->actingAs($anotherUser);

        $response = $this->patchJson(route("offices.update", $office->id), [
            "title" => "unauthorized"
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function itMarksTheOfficeAsPendingIfDirty()
    {
        Notification::fake();

        $admin = User::factory()->withRole(ROLE::ROLE_ADMIN)->create();

        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->withRole(ROLE::ROLE_USER)->create();

        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $this->patchJson(route("offices.update", $office->id), [
            "price_per_day" => 100
        ]);

        $this->assertDatabaseHas("offices", [
            "id" => $office->id,
            "approval_status" => Office::APPROVAL_PENDING
        ]);

        Notification::assertSentTo($admin, OfficeUpdatedNotification::class);
    }

    /**
     * @test
     */
    public function itCanDeleteOffices()
    {
        Storage::put("office_image.jpg", "empty");

        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->withRole(ROLE::ROLE_USER)->create();

        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            "path" => "office_image.jpg"
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson(route("offices.delete", $office->id));

        $response->assertOk();

        $this->assertSoftDeleted($office);

        $this->assertModelMissing($image);

        Storage::assertMissing("office_image.jpg");
    }

    /**
     * @test
     */

    public function itCannotDeleteAnOfficeThatHasReservations()
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->withRole(ROLE::ROLE_USER)->create();

        $office = Office::factory()->for($user)->create();

        Reservation::factory()->for($office)->create(["status" => Reservation::STATUS_ACTIVE]);

        $this->actingAs($user);

        $response = $this->deleteJson(route("offices.delete", $office->id));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNotSoftDeleted($office);
    }


    /**
     * @test
     */
    public function itUpdatetedTheFeatureImageOfAnOffice()
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->withRole(ROLE::ROLE_USER)->create();

        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            "path" => "image.jpg"
        ]);

        $this->actingAs($user);

        $response = $this->patchJson(route("offices.update", $office->id), [
            "featured_image_id" => $image->id
        ]);

        $response->assertOk()
            ->assertJsonPath("data.featured_image_id", $image->id);
    }

    /**
     * @test
     */

    public function itDoesntUpdateFeaturedImageThatBelongsToAnotherOffice()
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->withRole(ROLE::ROLE_USER)->create();

        $office = Office::factory()->for($user)->create();
        $office2 = Office::factory()->create();

        $image = $office2->images()->create([
            "path" => $this->faker->word . ".jpg"
        ]);

        $this->actingAs($user);

        $response = $this->patchJson(route("offices.update", $office->id), [
            "featured_image_id" => $image->id
        ]);

        $response->assertUnprocessable()
            ->assertInvalid("featured_image_id");
    }
}
