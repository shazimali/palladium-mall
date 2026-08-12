<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskCommentAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task,
        public TaskComment $comment,
        public User $commenter
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'task_comment_added',
            'task_id'        => $this->task->id,
            'comment_id'     => $this->comment->id,
            'title'          => $this->task->title,
            'commenter_id'   => $this->commenter->id,
            'commenter_name' => $this->commenter->name,
            'comment_preview' => mb_strimwidth($this->comment->comment, 0, 60, '...'),
            'message'        => "{$this->commenter->name} commented on '{$this->task->title}'",
            'url'            => route('tasks.index', ['task_id' => $this->task->id]),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
