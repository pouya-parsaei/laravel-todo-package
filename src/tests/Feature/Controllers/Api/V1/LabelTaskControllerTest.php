<?php

namespace PouyaParsaei\LaravelToDo\tests\Feature\Controllers\Api\V1;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PouyaParsaei\LaravelToDo\Models\Label;
use PouyaParsaei\LaravelToDo\Models\Task;
use PouyaParsaei\LaravelToDo\tests\TestingHelpers\Middlewares;
use PouyaParsaei\LaravelToDo\tests\TestingHelpers\TokenMaker;
use Tests\TestCase;

class LabelTaskControllerTest extends TestCase
{
    use RefreshDatabase, TokenMaker, Middlewares;

    public function testEnsureAuthUserCanAddLabelToHisOwnTask()
    {
        $user = factory(User::class)->create();
        $labels = factory(Label::class, random_int(1, 10))->create()->pluck('id');
        $userTask = $user->tasks()->create(
            factory(Task::class)->make()->toArray()
        );

        $data = ['labels' => $labels];
        $token = $this->createAuthorizationToken($user->api_token);

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->postJson(
                route('tasks.add-label', $userTask),
                $data
            );

        $this
            ->assertEquals(201, $response->getStatusCode());

        foreach ($labels as $label) {
            $this
                ->assertDatabaseHas('label_task', [
                    'task_id' => $userTask->id,
                    'label_id' => $label
                ]);
        }

        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );

        $response
            ->assertJson(json_decode($response->getContent(), true));

    }

    public function testEnsureAuthUserCanNotAddLabelToOtherUsersTask()
    {
        $user = factory(User::class)->create();
        $labels = factory(Label::class, rand(1, 10))->create()->pluck('id');
        $taskBelongsToOtherUser = factory(Task::class)->create();
        $data = ['labels' => $labels];
        $token = $this->createAuthorizationToken($user->api_token);


        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->postJson(
                route('tasks.add-label', $taskBelongsToOtherUser),
                $data
            );


        $this->assertEquals(403, $response->getStatusCode());
        $response->assertJson(json_decode($response->getContent(), true));

        foreach ($labels as $label) {
            $this
                ->assertDatabaseMissing('label_task', [
                    'task_id' => $taskBelongsToOtherUser->id,
                    'label_id' => $label
                ]);
        }

        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );

    }

    public function testEnsureLabelsSyncToTaskCorrectlyWhenTaskHasLabels()
    {
        $userLabels = factory(Label::class, random_int(1, 10))->create()->pluck('id');
        $user = factory(User::class)->create();
        $userTask = $user->tasks()->create(
            factory(Task::class)->states('withoutDefaultUserId')->make()->toArray()
        );
        $userTask->labels()->attach($userLabels);

        $newLabels = factory(Label::class, random_int(1, 10))->create()->pluck('id');

        $data = ['labels' => $newLabels];

        $token = $this->createAuthorizationToken($user->api_token);

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->postJson(
                route('tasks.add-label', $userTask),
                $data
            );

        $this
            ->assertEquals(201, $response->getStatusCode());

        $mergedLabels = $userLabels->merge($newLabels);

        foreach ($mergedLabels as $mergedLabel) {
            $this
                ->assertDatabaseHas('label_task', [
                    'task_id' => $userTask->id,
                    'label_id' => $mergedLabel
                ]);
        }

        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );

        $response
            ->assertJson(json_decode($response->getContent(), true));

    }

    public function testEnsureLabelsSyncToTaskCorrectlyWhenLabelIsDuplicated()
    {
        $user = factory(User::class)->create();
        $userTask = $user->tasks()->create(
            factory(Task::class)->states('withoutDefaultUserId')->make()->toArray()
        );
        $userLabels = factory(Label::class, random_int(1, 10))->create()->pluck('id');
        $userTask->labels()->attach($userLabels);

        $newLabels = factory(Label::class, random_int(1, 10))->create()->pluck('id');
        $mergedLabels = $userLabels->merge($newLabels);


        $data = ['labels' => $mergedLabels];

        $token = $this->createAuthorizationToken($user->api_token);

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->postJson(
                route('tasks.add-label', $userTask),
                $data
            );

        $taskLabelsAfterSync = Task::find($userTask->id)->labels()->count();
        $this->assertEquals($mergedLabels->count(), $taskLabelsAfterSync);
        foreach ($mergedLabels as $mergedLabel) {
            $this
                ->assertDatabaseHas('label_task', [
                    'task_id' => $userTask->id,
                    'label_id' => $mergedLabel
                ]);
        }

    }


    public function testValidationRequestRequiredDataForLabels()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $userTask = $user->tasks()->create(
            factory(Task::class)->make()->toArray()
        );
        $error = 'The labels field is required.';


        $data = [];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.add-label', $userTask), $data);
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['labels'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );
    }

    public function testValidationRequestMustBeArrayForLabels()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $userTask = $user->tasks()->create(
            factory(Task::class)->make()->toArray()
        );
        $error = 'The labels must be an array.';

        $data = ['labels' => 'lfdkjshfksj'];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.add-label', $userTask), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['labels'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );
    }

    public function testValidationRequestRequiredDataForLabelsItems()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $userTask = $user->tasks()->create(
            factory(Task::class)->make()->toArray()
        );
        $error = 'The labels.0 field is required.';

        $data = ['labels' => ['']];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.add-label', $userTask), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['labels.0'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );
    }

    public function testValidationRequestMustBeIntegerForLabelsItems()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $userTask = $user->tasks()->create(
            factory(Task::class)->make()->toArray()
        );
        $error = 'The labels.0 must be an integer.';

        $data = ['labels' => ['test test']];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.add-label', $userTask), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['labels.0'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );
    }

    public function testValidationRequestMinimumIntForLabelsItems()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $userTask = $user->tasks()->create(
            factory(Task::class)->make()->toArray()
        );
        $error = 'The labels.0 must be at least 1.';

        $data = ['labels' => [0]];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.add-label', $userTask), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['labels.0'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );
    }

    public function testValidationRequestLabelExistsForLabelsItems()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $userTask = $user->tasks()->create(
            factory(Task::class)->make()->toArray()
        );
        $error = 'The selected labels.0 is invalid.';

        $data = ['labels' => [1000]];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.add-label', $userTask), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['labels.0'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );
    }
}
