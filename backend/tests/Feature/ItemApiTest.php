<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_item()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/items', [
            'name' => 'Laptop ThinkPad',
            'quantity' => 5,
            'location' => 'Gudang A'
        ]);

        $response->assertStatus(201);
    }

    public function test_validation_fails_returns_422()
    {
        $user = User::factory()->create();
        
        // Sengaja mengirim data kosong agar validasi gagal
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/items', []);

        $response->assertStatus(422);
    }

    public function test_unauthorized_access_returns_401()
    {
        // Tidak mengirimkan token auth
        $response = $this->getJson('/api/items');

        $response->assertStatus(401);
    }
}