<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
use App\Traits\LogsActivity;

class PaymentAccount extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
 
    protected $fillable = [
        'name',
        'account_number',
        'account_holder',
        'bank_name',
        'is_active',
        'notes',
        'type',
    ];
 
    protected $casts = [
        'is_active' => 'boolean',
    ];
 
    /**
     * Get the payments associated with this payment account.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
