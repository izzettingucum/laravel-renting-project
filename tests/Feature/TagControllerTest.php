<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    /**
     * @test
     */
    public function itListsTags()
    {
        $response = $this->get('api/tags');

        $response->dump();

        $response->assertOk();
    }
}
