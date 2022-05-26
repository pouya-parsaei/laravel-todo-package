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
        $this->withoutExceptionHandling();
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

    public function testValidationRequestRequiredDataForLabels()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $userTask = $user->tasks()->create(
            factory(Task::class)->make()->toArray()
        );
        $errors = [
            'labels' => 'The labels field is required.',
        ];

        $data = [];

        $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.add-label', $userTask), $data)
            ->assertSessionHasErrors($errors);
    }

    public function testValidationRequestMustBeArrayForLabels()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $userTask = $user->tasks()->create(
            factory(Task::class)->make()->toArray()
        );
        $errors = [
            'labels' => 'The labels must be an array.',
        ];

        $data = ['labels' => 'lfdkjshfksj'];

        $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.add-label', $userTask), $data)
            ->assertSessionHasErrors($errors);
    }

}
