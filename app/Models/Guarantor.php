<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Guarantor extends Model
{
    protected $fillable = [
        'tenant_id',
        'agreement_id',
        'name',
        'cnic',
        'phone',
        'relation',
        'address',
        'occupation',
        'shop_name',
        'visiting_card_photo',
        'cnic_image',
        'cnic_front',
        'cnic_back',
        'photo',
        'visiting_card',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function getVisitingCardPhotoUrlAttribute(): ?string
    {
        return $this->visiting_card_photo
            ? Storage::disk('public')->url($this->visiting_card_photo)
            : null;
    }

    public function getCnicImageUrlAttribute(): ?string
    {
        return $this->cnic_image
            ? Storage::disk('public')->url($this->cnic_image)
            : null;
    }

    public function getCnicFrontUrlAttribute(): ?string
    {
        return $this->cnic_front
            ? Storage::disk('public')->url($this->cnic_front)
            : null;
    }

    public function getCnicBackUrlAttribute(): ?string
    {
        return $this->cnic_back
            ? Storage::disk('public')->url($this->cnic_back)
            : null;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo
            ? Storage::disk('public')->url($this->photo)
            : null;
    }

    public function getVisitingCardUrlAttribute(): ?string
    {
        return $this->visiting_card
            ? Storage::disk('public')->url($this->visiting_card)
            : null;
    }
}
