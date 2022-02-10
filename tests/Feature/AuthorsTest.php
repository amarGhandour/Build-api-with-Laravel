<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
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

        $author = Author::factory()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $headers = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];

        $this->getJson("/api/v1/authors/$author->id", $headers)
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
    public function it_can_sort_authors_by_name_through_a_sort_query_parameter()
    {

        // given we have three authors => Mane, Doe, Kane.
        //when user make a query by name
        // then sort them by name => Doe, Kane, Mane.

        $authors = collect([
            'Mane',
            'Doe',
            'Kane',
        ])->map(function ($name) {
            return Author::factory()->create([
                'name' => $name,
            ]);
        });

        $user = User::factory()->create();
        Sanctum::actingAs($user);


        $headers = [
            'Accept' => 'application/vnd.api+json',
        ];

        $this->getJson('/api/v1/authors?sort=name', $headers)->assertOk()->assertJson([
            'data' => [
                [
                    'data' => [
                        'type' => 'authors',
                        'id' => (string)$authors[1]->id,
                        'attributes' => [
                            'name' => $authors[1]->name,
                            'created_at' => $authors[1]->created_at->toDateTimeString(),
                            'updated_at' => $authors[1]->updated_at->toDateTimeString(),
                        ]
                    ]
                ],
                [
                    'data' => [
                        'type' => 'authors',
                        'id' => (string)$authors[2]->id,
                        'attributes' => [
                            'name' => $authors[2]->name,
                            'created_at' => $authors[2]->created_at->toDateTimeString(),
                            'updated_at' => $authors[2]->updated_at->toDateTimeString(),
                        ]
                    ]
                ],
                [
                    'data' => [
                        'type' => 'authors',
                        'id' => (string)$authors[0]->id,
                        'attributes' => [
                            'name' => $authors[0]->name,
                            'created_at' => $authors[0]->created_at->toDateTimeString(),
                            'updated_at' => $authors[0]->updated_at->toDateTimeString(),
                        ]
                    ]
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

        $headers = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];

        $this->getJson('/api/v1/authors', $headers)
            ->assertOk()->assertJson([
                'data' => [
                    [
                        'data' => [
                            'type' => 'authors',
                            'id' => $author->id,
                            'attributes' => [
                                'name' => $author->name,
                                'created_at' => Carbon::parse($author->created_at)->toDateTimeString(),
                                'updated_at' => Carbon::parse($author->created_at)->toDateTimeString(),
                            ],
                        ],
                    ]
                ],
                'links' => [
                    "first" => route('authors.index', [
                        'page' => 1
                    ]),
                    "last" => route('authors.index', [
                        'page' => 1
                    ]),
                    "prev" => null,
                    "next" => null
                ]
            ]);

    }


    /**
     * @test
     */
    public function it_can_create_an_author_from_a_resource_object()
    {

        $author = Author::factory()->make([
            "updated_at" => Carbon::now()->timestamp,
            "created_at" => Carbon::now()->timestamp,
        ]);

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $resourceObject = [
            'data' => [
                'type' => 'authors',
                'attributes' => $author,
            ]

        ];

        $headers = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];

        $response = $this->postJson('/api/v1/authors', $resourceObject, $headers);

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
    public function it_can_update_an_author_from_a_resource_object()
    {

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $author = Author::factory()->create([
            'name' => 'Doe',
            "updated_at" => Carbon::now()->timestamp,
            "created_at" => Carbon::now()->timestamp,
        ]);


        $headers = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];

        $this->patchJson("/api/v1/authors/$author->id", [
            'data' => [
                'type' => 'authors',
                'id' => $author->id,
                'attributes' => [
                    'name' => 'Jane',
                ],
            ]
        ], $headers)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'type' => 'authors',
                    'id' => $author->id,
                    'attributes' => [
                        'name' => 'Jane',
                        'created_at' => $author->created_at->toJson(),
                        'updated_at' => $author->updated_at->toJson(),
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
    public function it_can_delete_an_author_through_a_delete_request()
    {

        $author = Author::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $headers = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];

        $this->deleteJson("/api/v1/authors/$author->id", [], $headers)
            ->assertNoContent();

        $this->assertDatabaseMissing('authors', [
            'id' => $author->id,
            'name' => $author->name
        ]);

    }


    /**
     * @test
     */
    public function it_validates_that_the_type_member_is_given_when_creating_an_author()
    {


        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $author = Author::factory()->make();

        $headers = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];

        $this->postJson("/api/v1/authors", [
            'data' => [
                'type' => '',
                'id' => $author->id,
                'attributes' => [
                    'name' => 'Jane',
                ],
            ]
        ], $headers)->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source' => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseMissing('authors', [
            'name' => $author->name
        ]);

    }

    /**
     * @test
     */
    public function it_validates_that_the_type_member_value_is_authors_when_creating_an_author()
    {

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $author = Author::factory()->make();

        $headers = [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];

        $this->postJson("/api/v1/authors", [
            'data' => [
                'type' => 'author',
                'id' => $author->id,
                'attributes' => [
                    'name' => 'Jane',
                ],
            ]
        ], $headers)->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Validation Error',
                        'details' => 'The selected data.type is invalid.',
                        'source' => [
                            'pointer' => '/data/type',
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseMissing('authors', [
            'name' => $author->name
        ]);

    }


}
