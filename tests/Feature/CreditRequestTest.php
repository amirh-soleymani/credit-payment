<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreditRequestTest extends TestCase
{

    public function test_send_credit_request_returns_successful(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test',
            'email' => 'test@gmail.com',
            'password' => Hash::make('password'),
            'type' => 'client',
        ]);

        $token = $testUser->createToken('appAuthenticationToken')->accessToken;

        $testResponse = $this
            ->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/sendRequest', [
                'seller_id' => 6
            ]);

        $testResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Your Credit Request Registered Successfully'
            ])
            ->assertJsonStructure(
                [
                    'status',
                    'message',
                    'data' => [
                        'seller' => [
                            'id',
                            'name',
                            'email',
                            'type',
                            'creditScore',
                            'created_at',
                            'updated_at'
                        ],
                        'client' => [
                            'id',
                            'name',
                            'email',
                            'type',
                            'creditScore',
                            'created_at',
                            'updated_at'
                        ],
                        'status',
                        'created_at',
                    ],
                ]
            );
    }

    public function test_send_duplicate_credit_request_returns_error(): void
    {
        $testUser = User::where('email', 'test@gmail.com')
            ->first();

        $token = $testUser->createToken('appAuthenticationToken')->accessToken;

        $testResponse = $this
            ->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/sendRequest', [
                'seller_id' => 6
            ]);

        $testResponse->assertStatus(400)
            ->assertJson([
                'message' => 'You Have made Credit Request for This Seller Before'
            ]);
    }

    public function test_seller_credit_request_list_returns_error_on_client_type_user(): void
    {
        $testUser = User::where('email', 'testMember@gmail.com')
            ->first();

        $token = $testUser->createToken('appAuthenticationToken')->accessToken;

        $testResponse = $this
            ->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/sellerCreditRequestList');

        $testUser->delete();

        $testResponse->assertStatus(403)
            ->assertJson([
                'message' => 'Access Denied'
            ]);
    }

    public function test_seller_credit_request_list_returns_success(): void
    {
        $testUser = User::find(6);

        $token = $testUser->createToken('appAuthenticationToken')->accessToken;

        $testResponse = $this
            ->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/sellerCreditRequestList');

        $testResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Done'
            ])->assertJsonStructure(
                [
                    'status',
                    'message',
                    'data' => [
                        '*' => [
                            'seller' => [
                                'id',
                                'name',
                                'email',
                                'type',
                                'creditScore',
                                'created_at',
                                'updated_at'
                            ],
                            'client' => [
                                'id',
                                'name',
                                'email',
                                'type',
                                'creditScore',
                                'created_at',
                                'updated_at'
                            ],
                            'status',
                            'created_at',
                        ]
                    ],
                ]
            );
    }

    public function test_check_client_credit_not_enough_credit(): void
    {
        $testUser = User::find(6);

        $token = $testUser->createToken('appAuthenticationToken')->accessToken;

        $testResponse = $this
            ->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/checkClientCredit', [
                'credit_request_id' => $testUser->sellerRequest()->first()->id
            ]);

        $testResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Client Doesnt have Enough Score For Credit Payment'
            ])->assertJsonStructure(
                [
                    'status',
                    'message',
                    'data' => []
                ]
            );
    }

    public function test_check_client_credit_has_enough_credit(): void
    {
        $testUser = User::find(6);
        $testClient = User::where('email', 'test@gmail.com')->first();
        $testClient->creditScore = 20;
        $testClient->save();

        $token = $testUser->createToken('appAuthenticationToken')->accessToken;

        $testResponse = $this
            ->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/checkClientCredit', [
                'credit_request_id' => $testClient->clientRequest->first()->id
            ]);

        $testResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Client Has Enough Score For Credit Payment'
            ])->assertJsonStructure(
                [
                    'status',
                    'message',
                    'data' => []
                ]
            );
    }

    public function test_client_credit_request_list_returns_error_on_client_type_user(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test',
            'email' => 'testCheck@gmail.com',
            'password' => Hash::make('password'),
            'type' => 'seller'
        ]);

        $token = $testUser->createToken('appAuthenticationToken')->accessToken;

        $testResponse = $this
            ->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/clientCreditRequestList');

        $testUser->delete();

        $testResponse->assertStatus(403)
            ->assertJson([
                'message' => 'Access Denied'
            ]);
    }

    public function test_client_credit_request_list_returns_success(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test',
            'email' => 'testCheck@gmail.com',
            'password' => Hash::make('password'),
            'type' => 'client'
        ]);

        $token = $testUser->createToken('appAuthenticationToken')->accessToken;

        $testResponse = $this
            ->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/clientCreditRequestList');

        $testUser->delete();

        $testResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Done'
            ])->assertJsonStructure(
                [
                    'status',
                    'message',
                    'data' => [
                        '*' => [
                            'seller' => [
                                'id',
                                'name',
                                'email',
                                'type',
                                'creditScore',
                                'created_at',
                                'updated_at'
                            ],
                            'client' => [
                                'id',
                                'name',
                                'email',
                                'type',
                                'creditScore',
                                'created_at',
                                'updated_at'
                            ],
                            'status',
                            'created_at',
                        ]
                    ],
                ]
            );
    }

    public function test_seller_accept_credit_request(): void
    {
        $testUser = User::find(6);
        $testClient = User::where('email', 'test@gmail.com')->first();

        $token = $testUser->createToken('appAuthenticationToken')->accessToken;

        $testResponse = $this
            ->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/sellerAcceptCreditRequest', [
                'credit_request_id' => $testClient->clientRequest->first()->id
            ]);

        $testResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Credit Request Accepted Successfully.'
            ])->assertJsonStructure(
                [
                    'status',
                    'message',
                    'data' => [
                        'seller' => [
                            'id',
                            'name',
                            'email',
                            'type',
                            'creditScore',
                            'created_at',
                            'updated_at'
                        ],
                        'client' => [
                            'id',
                            'name',
                            'email',
                            'type',
                            'creditScore',
                            'created_at',
                            'updated_at'
                        ],
                        'status',
                        'created_at',
                    ],
                ]
            );
    }

    public function test_seller_deny_credit_request(): void
    {
        $testUser = User::find(6);
        $testClient = User::where('email', 'test@gmail.com')->first();

        $token = $testUser->createToken('appAuthenticationToken')->accessToken;

        $testResponse = $this
            ->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/sellerDenyCreditRequest', [
                'credit_request_id' => $testClient->clientRequest->first()->id
            ]);

        $testResponse->assertStatus(200)
            ->assertJsonStructure(
                [
                    'status',
                    'message',
                    'data' => [
                        'seller' => [
                            'id',
                            'name',
                            'email',
                            'type',
                            'creditScore',
                            'created_at',
                            'updated_at'
                        ],
                        'client' => [
                            'id',
                            'name',
                            'email',
                            'type',
                            'creditScore',
                            'created_at',
                            'updated_at'
                        ],
                        'status',
                        'created_at',
                    ],
                ]
            );

        $testClient = User::where('email', 'test@gmail.com')->first();
        $testClient->clientRequest()->delete();
        $testClient->delete();
    }

}
