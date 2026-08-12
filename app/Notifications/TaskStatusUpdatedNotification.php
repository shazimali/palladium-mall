<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task,
        public User $updater,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabels = [
            'todo'        => 'To Do',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
        ];

        $oldLabel = $statusLabels[$this->oldStatus] ?? $this->oldStatus;
        $newLabel = $statusLabels[$this->newStatus] ?? $this->newStatus;

        return [
            'type'         => 'task_status_updated',
            'task_id'      => $this->task->id,
            'title'        => $this->task->title,
            'old_status'   => $this->oldStatus,
            'new_status'   => $this->newStatus,
            'updater_id'   => $this->updater->id,
            'updater_name' => $this->updater->name,
            'message'      => "{$this->updater->name} moved task '{$this->task->title}' to {$newLabel}",
            'url'          => route('tasks.index', ['task_id' => $this->task->id]),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
