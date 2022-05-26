<?php

namespace PouyaParsaei\LaravelToDo\tests\Feature\Controllers\Api\V1;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PouyaParsaei\LaravelToDo\Models\Label;
use PouyaParsaei\LaravelToDo\Models\Task;
use PouyaParsaei\LaravelToDo\tests\TestingHelpers\Middlewares;
use PouyaParsaei\LaravelToDo\tests\TestingHelpers\TokenMaker;
use Tests\TestCase;

class LabelControllerTest extends TestCase
{
    use RefreshDatabase, TokenMaker, Middlewares;

    public function testEnsureAuthUserCanStoreLabel()
    {
        $data = factory(Label::class)
            ->make()
            ->toArray();
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->postJson(
                route('labels.store'),
                ['name' => $data['name']]
            );

        $response->assertJson(json_decode($response->getContent(), true));


        $this
            ->assertDatabaseHas('labels', $data);


        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );


        $this
            ->assertEquals(201, $response->getStatusCode());

    }

    public function testValidationRequestRequiredData()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = ['The name field is required.'];
        $data = [];


        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('labels.store'), $data);

        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals($error, $responseData['name']);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestNameMustBeString()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = 'The name must be a string.';

        $data = ['name' => 1];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('labels.store'), $data);

        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals($error, $responseData['name'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMinimumCharactersDataRule()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = 'The name must be at least 2 characters.';
        $data = ['name' => Str::random(1)];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('labels.store'), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['name'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMaximumCharactersDataRule()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = 'The name may not be greater than 128 characters.';
        $data = ['name' => Str::random(129)];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('labels.store'), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['name'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestUniqueDataRule()
    {
        $user = factory(User::class)->create();
        $label = factory(Label::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = 'The name has already been taken.';
        $data = ['name' => $label->name];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('labels.store'), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['name'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);

    }

    public function testEnsureAuthUserCanGetLabels()
    {
        foreach (range(2, 5) as $item) {
            Label::create(['name' => 'test' . rand(1000, 3000)]);
        }
        $user = User::create([
            'name' => 'test' . rand(10, 30),
            'email' => 'test' . rand(10, 30),
            'email_verified_at' => now(),
            'api_token' => Str::random(60),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10)

        ]);
        foreach (range(1, 3) as $item) {
            $task = Task::create([
                'user_id' => $user->id,
                'title' => 'test' . rand(1, 5),
                'description' => 'test' . rand(2, 6)
            ]);
            Label::inRandomOrder()->first()->tasks()->attach($task);
        }

        $user = User::inRandomOrder()->first();

        $token = $this->createAuthorizationToken($user->api_token);

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->get(route('labels.index'));

        $labelsInResponse = json_decode($response->getContent(), true)['data'];
        $totalLabelsInDatabase = Label::count();

        $this->assertEquals($totalLabelsInDatabase, count($labelsInResponse));


        foreach ($labelsInResponse as $labelInResponse) {
            $this->assertArrayHasKey('id', $labelInResponse);
            $this->assertArrayHasKey('label', $labelInResponse);
            $this->assertArrayHasKey('total_tasks_having_this_label', $labelInResponse);

            $userTaskLabels = Label::find($labelInResponse['id'])->count_auth_user_tasks;
            $this->assertEquals($userTaskLabels, $labelInResponse['total_tasks_having_this_label']);
        }

        $response->assertJson(json_decode($response->getContent(), true));

        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );

        $this
            ->assertEquals(200, $response->getStatusCode());
    }

}
