<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SimpleTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function homepage_returns_ok(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
