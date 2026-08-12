<?php

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed task permissions & admin role
    $this->artisan('db:seed', ['--class' => 'TaskPermissionSeeder']);
});

test('admin can view task board', function () {
    $user = User::factory()->create();
    $adminRole = Role::where('name', 'admin')->first();
    $user->assignRole($adminRole);

    $response = $this->actingAs($user)->get(route('tasks.index'));
    $response->assertStatus(200);
    $response->assertSee('Task Management Board');
});

test('admin can create and assign task to another registered admin', function () {
    $assigner = User::factory()->create();
    $assignee = User::factory()->create();
    $adminRole = Role::where('name', 'admin')->first();
    $assigner->assignRole($adminRole);
    $assignee->assignRole($adminRole);

    $taskData = [
        'title'        => 'Inspect Shop 102 Electrical Meter',
        'description'  => 'Please verify breaker status and reading.',
        'priority'     => 'high',
        'due_at'       => now()->addDays(2)->toDateTimeString(),
        'assignee_ids' => [$assignee->id],
    ];

    $response = $this->actingAs($assigner)->postJson(route('tasks.store'), $taskData);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('tasks', [
        'title'      => 'Inspect Shop 102 Electrical Meter',
        'priority'   => 'high',
        'status'     => 'todo',
        'created_by' => $assigner->id,
    ]);

    $task = Task::first();
    expect($task->assignees)->toHaveCount(1);
    expect($task->assignees->first()->id)->toBe($assignee->id);
});

test('assignee can update task status across 3 columns', function () {
    $assigner = User::factory()->create();
    $assignee = User::factory()->create();
    $adminRole = Role::where('name', 'admin')->first();
    $assigner->assignRole($adminRole);
    $assignee->assignRole($adminRole);

    $task = Task::create([
        'title'      => 'Fix Water Leakage in Basement',
        'status'     => 'todo',
        'priority'   => 'urgent',
        'created_by' => $assigner->id,
    ]);
    $task->assignees()->attach($assignee->id);

    // Move to In Progress
    $res1 = $this->actingAs($assignee)->patchJson(route('tasks.update-status', $task), [
        'status' => 'in_progress',
    ]);
    $res1->assertStatus(200);
    expect($task->fresh()->status)->toBe('in_progress');
    expect($task->fresh()->completed_at)->toBeNull();

    // Move to Completed
    $res2 = $this->actingAs($assignee)->patchJson(route('tasks.update-status', $task), [
        'status' => 'completed',
    ]);
    $res2->assertStatus(200);
    expect($task->fresh()->status)->toBe('completed');
    expect($task->fresh()->completed_at)->not->toBeNull();
});

test('assigner and assignee can post comments on a task', function () {
    $assigner = User::factory()->create();
    $assignee = User::factory()->create();
    $adminRole = Role::where('name', 'admin')->first();
    $assigner->assignRole($adminRole);
    $assignee->assignRole($adminRole);

    $task = Task::create([
        'title'      => 'Audit Gate Pass Entries',
        'status'     => 'in_progress',
        'priority'   => 'medium',
        'created_by' => $assigner->id,
    ]);
    $task->assignees()->attach($assignee->id);

    // Assignee comments
    $res1 = $this->actingAs($assignee)->postJson(route('tasks.comments.store', $task), [
        'comment' => 'Checked first 10 entries. All good!',
    ]);
    $res1->assertStatus(200);
    expect(TaskComment::count())->toBe(1);

    // Assigner comments back
    $res2 = $this->actingAs($assigner)->postJson(route('tasks.comments.store', $task), [
        'comment' => 'Great work! Please check the rest today.',
    ]);
    $res2->assertStatus(200);
    expect(TaskComment::count())->toBe(2);
});
