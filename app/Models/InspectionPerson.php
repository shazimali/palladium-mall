<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class InspectionPerson extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'inspection_persons';

    protected $fillable = [
        'name',
        'designation',
        'phone',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the checklists performed by this inspection person.
     */
    public function checklists(): HasMany
    {
        return $this->hasMany(MoveInChecklist::class, 'inspection_person_id');
    }
}
