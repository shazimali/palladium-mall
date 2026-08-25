<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class Task extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'category_id',
        'description',
        'remarks',
        'creator_remarks',
        'creator_rating',
        'admin_photo',
        'assignee_remarks',
        'status',
        'priority',
        'due_at',
        'completed_at',
        'created_by',
        'order_column',
    ];

    protected $appends = [
        'admin_photo_url',
    ];

    public function getAdminPhotoUrlAttribute(): ?string
    {
        return $this->admin_photo ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->admin_photo) : null;
    }

    protected function casts(): array
    {
        return [
            'due_at'       => 'datetime',
            'completed_at' => 'datetime',
            'order_column' => 'integer',
        ];
    }

    /**
     * The user who created the task (Assigner).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The category this task belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    /**
     * The users assigned to this task (Assignees).
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees')->withTimestamps();
    }

    /**
     * Comments left on this task.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    /**
     * Check if a given user is involved with the task (creator or assignee).
     */
    public function isUserInvolved(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($this->created_by === $user->id) {
            return true;
        }

        return $this->assignees->contains('id', $user->id);
    }
}
