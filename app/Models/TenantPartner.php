<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'father_name',
        'cnic',
        'gender',
        'marital_status',
        'phone',
        'whatsapp_number',
        'email',
        'address',
        'occupation',
        'monthly_income',
        'passport_photo',
        'cnic_front_image',
        'cnic_back_image',
    ];

    protected $casts = [
        'monthly_income' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getPassportPhotoUrlAttribute(): ?string
    {
        return $this->passport_photo
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->passport_photo)
            : null;
    }

    public function getCnicFrontUrlAttribute(): ?string
    {
        return $this->cnic_front_image
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->cnic_front_image)
            : null;
    }

    public function getCnicBackUrlAttribute(): ?string
    {
        return $this->cnic_back_image
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->cnic_back_image)
            : null;
    }
}
