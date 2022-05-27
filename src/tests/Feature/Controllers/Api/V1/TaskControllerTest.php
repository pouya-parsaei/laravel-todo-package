<?php

namespace PouyaParsaei\LaravelToDo\tests\Feature\Controllers\Api\V1;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use PouyaParsaei\LaravelToDo\Consts\TaskStatus;
use PouyaParsaei\LaravelToDo\Models\Label;
use PouyaParsaei\LaravelToDo\Models\Task;
use PouyaParsaei\LaravelToDo\Notifications\TaskClosed;
use PouyaParsaei\LaravelToDo\tests\TestingHelpers\Middlewares;
use PouyaParsaei\LaravelToDo\tests\TestingHelpers\TokenMaker;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase, TokenMaker, Middlewares;

    public function testEnsureAuthUserCanStoreTask()
    {
        $this->withoutExceptionHandling();
        $data = factory(Task::class)
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
                route('tasks.store'),
                $data
            );
        $task = json_decode($response->getContent(), true)['data'];

        $this->assertEquals(201, $response->getStatusCode());
        $response->assertJson(json_decode($response->getContent(), true));
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        $this
            ->assertDatabaseHas('tasks', $task);

        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );


    }

    public function testEnsureAuthUserCanUpdateHisOwnTask()
    {
        $data = factory(Task::class)
            ->make()
            ->toArray();
        $user = factory(User::class)->create();
        $userTask = $user->tasks()->create(
            factory(Task::class)->make()->toArray()
        );
        $token = $this->createAuthorizationToken($user->api_token);

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->putJson(
                route('tasks.update', $userTask),
                [
                    'title' => $data['title'],
                    'description' => $data['description']
                ]
            );
        $updatedTask = json_decode($response->getContent(), true)['data'];

        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJson(json_decode($response->getContent(), true));
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        $this
            ->assertDatabaseHas('tasks', $updatedTask);

        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );

    }

    public function testUserCanNotUpdateOtherUsersTasks()
    {
        $data = factory(Task::class)
            ->make()
            ->toArray();
        $user = factory(User::class)->create();
        $taskBelongsToOtherUser = factory(Task::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->putJson(
                route('tasks.update', $taskBelongsToOtherUser),
                $data
            );


        $this->assertEquals(403, $response->getStatusCode());
        $response->assertJson(json_decode($response->getContent(), true));
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        $this
            ->assertDatabaseHas('tasks', $taskBelongsToOtherUser->toArray());

        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );
    }

    public function testEnsureAuthUserCanOpenATask()
    {
        $user = factory(User::class)->create();
        $task = $user->tasks()->create(
            factory(Task::class)->states('close')->make()->toArray()
        );
        $token = $this->createAuthorizationToken($user->api_token);
        $data = ['status' => TaskStatus::OPEN];

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->putJson(
                route('tasks.open-status', $task->id),
                $data
            );

        $taskInResponse = json_decode($response->getContent(), true)['data'];

        $taskafterUpdate = Task::find($task->id)->toArray();

        $this
            ->assertEquals(
                200,
                $response->getStatusCode()
            );

        $this
            ->assertEquals(
                $data['status'],
                $taskInResponse['status']
            );

        $this
            ->assertDatabaseHas('tasks', $taskafterUpdate);

        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );

        $response
            ->assertJson(json_decode($response->getContent(), true));

        $response
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function testEnsureAuthUserCanCloseATask()
    {
        $user = factory(User::class)->create();
        $task = $user->tasks()->create(
            factory(Task::class)->states('close')->make()->toArray()
        );
        $token = $this->createAuthorizationToken($user->api_token);
        $data = ['status' => TaskStatus::CLOSE];

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->putJson(
                route('tasks.close-status', $task->id),
                $data
            );



        $taskInResponse = json_decode($response->getContent(), true)['data'];
        $taskAfterUpdate = Task::find($task->id)->toArray();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($data['status'], $taskInResponse['status']);
        $response->assertJson(json_decode($response->getContent(), true));
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
        $this
            ->assertDatabaseHas('tasks', $taskAfterUpdate);

        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );
    }

    public function testEnsureNotificationSendWhenAuthUserCloseTask()
    {
        $user = factory(User::class)->create();
        $task = $user->tasks()->create(
            factory(Task::class)->states('close')->make()->toArray()
        );
        $token = $this->createAuthorizationToken($user->api_token);
        $data = ['status' => TaskStatus::CLOSE];

        Notification::fake();

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->putJson(
                route('tasks.close-status', $task->id),
                $data
            );

        Notification::assertSentTo(
            [$user], TaskClosed::class
        );

        $updatedTask = json_decode($response->getContent(), true)['data'];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($data['status'], $updatedTask['status']);
        $response->assertJson(json_decode($response->getContent(), true));
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
        $this
            ->assertDatabaseHas('tasks', $updatedTask);

        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );
    }

    public function testEnsureAuthUserCanGetDetailsOfATask()
    {
        $user = factory(User::class)->create();
        $userTask = $user->tasks()->create(
            factory(Task::class)->make()->toArray()
        );
        $token = $this->createAuthorizationToken($user->api_token);


        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->get(route('tasks.show', $userTask));


        $returnedTask = json_decode($response->getContent(), true)['data'];
        $this->assertEquals($userTask->toArray(), $returnedTask);
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJson(json_decode($response->getContent(), true));
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );
    }

    public function testEnsureAuthUserCanNotGetDetailsOfOtherUserTask()
    {
        $user = factory(User::class)->create();
        $taskBelongsToOtherUser = factory(Task::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->get(route('tasks.show', $taskBelongsToOtherUser));


        $this->assertEquals(403, $response->getStatusCode());
        $response->assertJson(json_decode($response->getContent(), true));
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);


        $this
            ->assertEquals(
                request()->route()->middleware(),
                $this->middlewares
            );
    }



    public function testEnsureAuthUserCanGetHisOwnTasks()
    {
        $this->withoutExceptionHandling();
        foreach (range(2, 5) as $item) {
            Label::create(['name' => 'test' . rand(1000, 3000)]);
        }
        $user = User::create([
            'name' => 'test' . rand(1000, 3000),
            'email' => 'test' . rand(1000, 3000),
            'email_verified_at' => now(),
            'api_token' => Str::random(60),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10)

        ]);
        foreach (range(1, 3) as $item) {
            $task = Task::create([
                'user_id' => $user->id,
                'title' => 'test' . rand(1000, 5000),
                'description' => 'test' . rand(2000, 6000)
            ]);
            Label::inRandomOrder()->first()->tasks()->attach($task);
        }

        $token = $this->createAuthorizationToken($user->api_token);

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->get(route('tasks.index'));


        $tasksInResponse = json_decode($response->getContent(), true)['data'];
        $totalUserTasksInDatabase = Task::where('user_id', auth()->user()->id)->count();

        $this->assertEquals($totalUserTasksInDatabase, count($tasksInResponse));


        foreach ($tasksInResponse as $taskInResponse) {
            $this->assertArrayHasKey('id', $taskInResponse);
            $this->assertArrayHasKey('title', $taskInResponse);
            $this->assertArrayHasKey('description', $taskInResponse);
            $this->assertArrayHasKey('labels', $taskInResponse);

            $taskFoundWithTaskInResponseId = Task::find($taskInResponse['id']);
            $this->assertNotNull($taskFoundWithTaskInResponseId);
            $this->assertEquals($taskFoundWithTaskInResponseId->title,$taskInResponse['title']);
            $this->assertEquals($taskFoundWithTaskInResponseId->description,$taskInResponse['description']);

            foreach ($taskInResponse['labels'] as $label) {
                $userTaskLabels = Label::find($label['id'])->count_auth_user_tasks;
                $this->assertEquals($userTaskLabels, $label['total_tasks_having_this_label']);
            }
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

    public function testEnsureAuthUserCanNotGetOtherUsersTasks()
    {
        $label = Label::create(['name' => 'test' . rand(10, 30)]);
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
            $label->tasks()->attach($task);
        }

        $taskIdsBelongToOtherUser = factory(Task::class, 10)->create()->pluck('id');


        $token = $this->createAuthorizationToken($user->api_token);

        $response = $this->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->get(route('tasks.index'));


        $tasksInResponse = json_decode($response->getContent(), true)['data'];
        $totalUserTasksInDatabase = Task::where('user_id', auth()->user()->id)->count();

        $this->assertEquals($totalUserTasksInDatabase, count($tasksInResponse));


        foreach ($tasksInResponse as $taskInResponse) {
            $this->assertArrayHasKey('id', $taskInResponse);
            $this->assertArrayHasKey('title', $taskInResponse);
            $this->assertArrayHasKey('description', $taskInResponse);
            $this->assertArrayHasKey('labels', $taskInResponse);

            $this->assertNotContains($taskInResponse['id'], $taskIdsBelongToOtherUser);


            foreach ($taskInResponse['labels'] as $label) {
                $userTaskLabels = Label::find($label['id'])->count_auth_user_tasks;
                $this->assertEquals($userTaskLabels, $label['total_tasks_having_this_label']);
            }
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

    public function testValidationRequestRequiredDataForTitleInStoreRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = 'The title field is required.';

        $data = [];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.store'), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['title'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMustBeStringForTitleInStoreRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = 'The title must be a string.';

        $data = ['title' => 1];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.store'), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['title'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMinimumCharactersDataRuleForTitleInStoreRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = 'The title must be at least 2 characters.';

        $data = ['title' => Str::random(1)];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.store'), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['title'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMaximumCharactersDataRuleForTitleInStoreRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = 'The title may not be greater than 128 characters.';

        $data = ['title' => Str::random(129)];

        $response  = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.store'), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['title'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }


    public function testValidationRequestMustBeStringForDescriptionInStoreRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = 'The description must be a string.';

        $data = ['description' => 1];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.store'), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['description'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMinimumCharactersDataRuleForDescriptionInStoreRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = 'The description must be at least 2 characters.';

        $data = ['description' => Str::random(1)];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.store'), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['description'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMaximumCharactersDataRuleForDescriptionInStoreRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);

        $error = 'The description may not be greater than 1000 characters.';

        $data = ['description' => Str::random(rand(1001, 1500))];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->post(route('tasks.store'), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['description'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }


    public function testValidationRequestRequiredDataForTitleInUpdateRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $task = factory(Task::class)->create();

        $error = 'The title field is required.';

        $data = [];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->put(route('tasks.update', $task->id), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['title'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMustBeStringForTitleInUpdateRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $task = factory(Task::class)->create();

        $error = 'The title must be a string.';

        $data = ['title' => 1];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->put(route('tasks.update', $task->id), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['title'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMinimumCharactersDataRuleForTitleInUpdateRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $task = factory(Task::class)->create();

        $error = 'The title must be at least 2 characters.';

        $data = ['title' => Str::random(1)];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->put(route('tasks.update', $task->id), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['title'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMaximumCharactersDataRuleForTitleInUpdateRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $task = factory(Task::class)->create();

        $error = 'The title may not be greater than 128 characters.';

        $data = ['title' => Str::random(129)];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->put(route('tasks.update', $task->id), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['title'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }


    public function testValidationRequestMustBeStringForDescriptionInUpdateRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $task = factory(Task::class)->create();

        $error = 'The description must be a string.';

        $data = ['description' => 1];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->put(route('tasks.update', $task->id), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['description'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMinimumCharactersDataRuleForDescriptionInUpdateRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $task = factory(Task::class)->create();

        $error = 'The description must be at least 2 characters.';

        $data = ['description' => Str::random(1)];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->put(route('tasks.update', $task->id), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['description'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }

    public function testValidationRequestMaximumCharactersDataRuleForDescriptionInUpdateRequest()
    {
        $user = factory(User::class)->create();
        $token = $this->createAuthorizationToken($user->api_token);
        $task = factory(Task::class)->create();

        $error = 'The description may not be greater than 1000 characters.';

        $data = ['description' => Str::random(rand(1001, 1500))];

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ])
            ->put(route('tasks.update', $task->id), $data);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($error, $responseData['description'][0]);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson($responseData);
    }


}
