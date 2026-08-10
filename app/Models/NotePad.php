<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class NotePad extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'date',
        'color',
        'is_pinned',
        'is_checklist',
        'status',
    ];

    protected $casts = [
        'date'         => 'date',
        'is_pinned'    => 'boolean',
        'is_checklist' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get checklist items as array if is_checklist is true.
     */
    public function getChecklistItemsAttribute(): array
    {
        if (!$this->is_checklist || empty($this->content)) {
            return [];
        }

        $decoded = json_decode($this->content, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set checklist items as JSON string.
     */
    public function setChecklistItemsAttribute(array $items): void
    {
        $this->content = json_encode($items);
    }

    /**
     * Calculate completed checklist count.
     */
    public function getCompletedTasksCountAttribute(): int
    {
        if (!$this->is_checklist) {
            return 0;
        }

        $items = $this->checklist_items;
        return count(array_filter($items, fn($item) => !empty($item['completed'])));
    }

    /**
     * Calculate total checklist count.
     */
    public function getTotalTasksCountAttribute(): int
    {
        if (!$this->is_checklist) {
            return 0;
        }

        return count($this->checklist_items);
    }

    /**
     * Hex background color inline style fallback for reliable rendering.
     */
    public function getBgStyleAttribute(): string
    {
        return match ($this->color) {
            'yellow' => 'background-color: #fef9c3;',
            'blue'   => 'background-color: #e0f2fe;',
            'green'  => 'background-color: #dcfce7;',
            'pink'   => 'background-color: #ffe4e6;',
            'purple' => 'background-color: #f3e8ff;',
            'orange' => 'background-color: #ffedd5;',
            default  => '',
        };
    }

    /**
     * Return Tailwind CSS background & border classes for Google Keep themes.
     */
    public function getColorClassesAttribute(): array
    {
        return match ($this->color) {
            'yellow' => [
                'card'   => 'bg-amber-100 dark:bg-amber-950/60 border-amber-300 dark:border-amber-700/60 text-amber-950 dark:text-amber-100',
                'header' => 'text-amber-950 dark:text-amber-100',
                'badge'  => 'bg-amber-200 text-amber-950 dark:bg-amber-900 dark:text-amber-100',
            ],
            'blue' => [
                'card'   => 'bg-sky-100 dark:bg-sky-950/60 border-sky-300 dark:border-sky-700/60 text-sky-950 dark:text-sky-100',
                'header' => 'text-sky-950 dark:text-sky-100',
                'badge'  => 'bg-sky-200 text-sky-950 dark:bg-sky-900 dark:text-sky-100',
            ],
            'green' => [
                'card'   => 'bg-emerald-100 dark:bg-emerald-950/60 border-emerald-300 dark:border-emerald-700/60 text-emerald-950 dark:text-emerald-100',
                'header' => 'text-emerald-950 dark:text-emerald-100',
                'badge'  => 'bg-emerald-200 text-emerald-950 dark:bg-emerald-900 dark:text-emerald-100',
            ],
            'pink' => [
                'card'   => 'bg-rose-100 dark:bg-rose-950/60 border-rose-300 dark:border-rose-700/60 text-rose-950 dark:text-rose-100',
                'header' => 'text-rose-950 dark:text-rose-100',
                'badge'  => 'bg-rose-200 text-rose-950 dark:bg-rose-900 dark:text-rose-100',
            ],
            'purple' => [
                'card'   => 'bg-purple-100 dark:bg-purple-950/60 border-purple-300 dark:border-purple-700/60 text-purple-950 dark:text-purple-100',
                'header' => 'text-purple-950 dark:text-purple-100',
                'badge'  => 'bg-purple-200 text-purple-950 dark:bg-purple-900 dark:text-purple-100',
            ],
            'orange' => [
                'card'   => 'bg-orange-100 dark:bg-orange-950/60 border-orange-300 dark:border-orange-700/60 text-orange-950 dark:text-orange-100',
                'header' => 'text-orange-950 dark:text-orange-100',
                'badge'  => 'bg-orange-200 text-orange-950 dark:bg-orange-900 dark:text-orange-100',
            ],
            default => [
                'card'   => 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800 text-gray-900 dark:text-white',
                'header' => 'text-gray-900 dark:text-white',
                'badge'  => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            ],
        };
    }
}
