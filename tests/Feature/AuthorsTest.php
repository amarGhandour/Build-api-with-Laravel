<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function it_returns_an_author_as_a_resource_object()
    {

//        $this->withoutExceptionHandling();

        $author = Author::factory()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/authors/$author->id")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'type' => 'authors',
                    'id' => (string)$author->id,
                    'attributes' => [
                        'name' => $author->name,
                        'created_at' => $author->created_at->toJson(),
                        'updated_at' => $author->updated_at->toJson(),
                    ]
                ]
            ]);
    }

    /**
     * @test
     */
    public function it_returns_all_authors_as_a_collection_of_resource_object()
    {

        $author = Author::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/authors')->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'data' => [
                            'type' => 'authors',
                            'id' => (string)$author->id,
                            'attributes' => [
                                'name' => $author->name,
                                'created_at' => $author->created_at->toJson(),
                                'updated_at' => $author->updated_at->toJson(),
                            ]
                        ],
                    ]
                ]
            ]);
    }


    /**
     * @test
     */
    public function it_can_create_an_author_from_a_resource_object()
    {

        $author = Author::factory()->make();

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $resourceObject = [
            'data' => [
                'type' => 'authors',
                'attributes' => $author,
            ]

        ];

        $response = $this->postJson('/api/v1/authors', $resourceObject);

        $author = Author::firstOrFail();

            $response->assertCreated()
            ->assertJson([
                'data' => [
                    'type' => 'authors',
                    'id' => $author->id,
                    'attributes' => [
                        'name' => $author->name,
                        'created_at' => now()->setMilliseconds(0)->toJson(),
                        'updated_at' => now()->setMilliseconds(0)->toJson()
                    ],
                ]
            ])->assertHeader('Location', url("api/v1/authors/$author->id"));

        $this->assertDatabaseHas('authors', [
           'name' => $author->name,
        ]);
    }


    /**
     * @test
     */
    public function it_can_update_an_author_from_a_resource_object(){

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $author = Author::factory()->create([
            'name' => 'Doe',
        ]);

        $this->patchJson("/api/v1/authors/$author->id", [
            'data' => [
                'type' => 'authors',
                'id' => $author->id,
                'attributes' => [
                    'name' => 'Jane',
                ],
            ]
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    'type' => 'authors',
                    'id' => $author->id,
                    'attributes' => [
                        'name' => 'Jane',
                        'created_at' => $author->created_at->toJson(),
                        'updated_at' => now()->setMilliseconds(0)->toJson()
                    ],
                ]
            ])->assertHeader('Location', url("api/v1/authors/$author->id"));

        $this->assertDatabaseHas('authors', [
           'id' => $author->id,
           'name' => 'Jane'
        ]);
    }

    /**
     * @test
     */
    public function it_can_delete_an_author_through_a_delete_request(){

        $author = Author::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/authors/$author->id")
              ->assertNoContent();

        $this->assertDatabaseMissing('authors', [
           'id' => $author->id,
           'name' => $author->name
        ]);

    }

}
