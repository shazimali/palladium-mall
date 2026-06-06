<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            static::logModelAction($model, 'created');
        });

        static::updated(function (Model $model) {
            static::logModelAction($model, 'updated');
        });

        static::deleted(function (Model $model) {
            static::logModelAction($model, 'deleted');
        });
    }

    protected static function logModelAction(Model $model, string $action)
    {
        $user = auth()->user();
        $userName = $user ? $user->name : 'System';
        
        $modelName = class_basename($model);
        
        $identifier = '';
        if (isset($model->unit_number)) {
            $identifier = "'{$model->unit_number}'";
        } elseif (isset($model->name)) {
            $identifier = "'{$model->name}'";
        } elseif (isset($model->title)) {
            $identifier = "'{$model->title}'";
        } elseif (isset($model->email)) {
            $identifier = "'{$model->email}'";
        } else {
            $identifier = "#{$model->getKey()}";
        }

        $description = "{$userName} {$action} {$modelName} {$identifier}";

        $properties = [];
        $ignoredKeys = ['password', 'remember_token', 'updated_at', 'created_at', 'deleted_at'];

        if ($action === 'updated') {
            $dirty = $model->getDirty();
            $old = [];
            $new = [];

            foreach ($dirty as $key => $value) {
                if (in_array($key, $ignoredKeys, true)) {
                    continue;
                }
                
                $friendlyKey = static::getFriendlyKey($key);
                $oldRaw = $model->getOriginal($key);
                
                $old[$friendlyKey] = static::resolveValue($model, $key, $oldRaw);
                $new[$friendlyKey] = static::resolveValue($model, $key, $value);
            }

            if (empty($new)) {
                return;
            }

            $properties['old'] = $old;
            $properties['new'] = $new;
        } elseif ($action === 'created') {
            $attributes = $model->getAttributes();
            $new = [];

            foreach ($attributes as $key => $value) {
                if (in_array($key, $ignoredKeys, true)) {
                    continue;
                }
                
                $friendlyKey = static::getFriendlyKey($key);
                $new[$friendlyKey] = static::resolveValue($model, $key, $value);
            }

            $properties['new'] = $new;
        } elseif ($action === 'deleted') {
            $attributes = $model->getAttributes();
            $old = [];

            foreach ($attributes as $key => $value) {
                if (in_array($key, $ignoredKeys, true)) {
                    continue;
                }
                
                $friendlyKey = static::getFriendlyKey($key);
                $old[$friendlyKey] = static::resolveValue($model, $key, $value);
            }

            $properties['old'] = $old;
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected static function getFriendlyKey(string $key): string
    {
        $friendlyKeys = [
            'unit_number' => 'Flat / Shop Number',
            'floor_id' => 'Floor',
            'block_id' => 'Block',
            'area_id' => 'Area / Zone',
            'type' => 'Property Type',
            'status' => 'Status',
            'area_sqft' => 'Area (sq. ft.)',
            'landlord_id' => 'Owner / Landlord',
            'tenant_id' => 'Tenant Name',
            'unit_id' => 'Flat / Shop',
            'agreement_id' => 'Agreement',
            'payment_account_id' => 'Payment Account',
            'user_id' => 'User Account',
            'is_active' => 'Active Status',
            'amount' => 'Amount Due',
            'amount_paid' => 'Amount Paid',
            'payment_method' => 'Payment Method',
            'reference' => 'Reference / Receipt Number',
            'paid_at' => 'Date of Payment',
            'due_date' => 'Due Date',
            'month' => 'Billing Month',
            'notes' => 'Notes / Remarks',
            'cnic' => 'CNIC / ID Number',
            'phone' => 'Phone Number',
            'email' => 'Email Address',
            'address' => 'Physical Address',
            'start_date' => 'Lease Start Date',
            'end_date' => 'Lease End Date',
            'monthly_rent' => 'Monthly Rent Amount',
            'security_deposit' => 'Security Deposit Amount',
            'maintenance_charge' => 'Monthly Maintenance Charge',
            'father_name' => 'Father\'s Name',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'emergency_contact' => 'Emergency Contact',
            'guarantor' => 'Guarantor Name',
            'date' => 'Creation Date',
        ];

        return $friendlyKeys[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    protected static function resolveValue(Model $model, string $key, $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        // If it's a foreign key ID, resolve the related model's name/title
        if (str_ends_with($key, '_id')) {
            $relationName = substr($key, 0, -3); // e.g. floor_id -> floor
            $relationMethod = \Illuminate\Support\Str::camel($relationName);
            
            if (method_exists($model, $relationMethod)) {
                try {
                    $relation = $model->$relationMethod();
                    if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                        $relatedClass = get_class($relation->getRelated());
                        $relatedInstance = $relatedClass::find($value);
                        if ($relatedInstance) {
                            return $relatedInstance->name 
                                ?? $relatedInstance->unit_number 
                                ?? $relatedInstance->email 
                                ?? (string)$value;
                        }
                    }
                } catch (\Exception $e) {
                    // Fail silently and return original ID
                }
            }
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}
