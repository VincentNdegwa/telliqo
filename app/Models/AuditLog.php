<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'user_email',
        'auditable_type',
        'auditable_id',
        'event',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'changes',
        'url',
        'method',
        'additional_data',
        'tags',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model (polymorphic).
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Decrypt and get old values.
     */
    public function getOldValuesAttribute($value): ?array
    {
        if (empty($value)) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($value);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt old_values in AuditLog', [
                'audit_log_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Encrypt and set old values.
     */
    public function setOldValuesAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['old_values'] = null;
            return;
        }

        $this->attributes['old_values'] = Crypt::encryptString(json_encode($value));
    }

    /**
     * Decrypt and get new values.
     */
    public function getNewValuesAttribute($value): ?array
    {
        if (empty($value)) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($value);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt new_values in AuditLog', [
                'audit_log_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Encrypt and set new values.
     */
    public function setNewValuesAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['new_values'] = null;
            return;
        }

        $this->attributes['new_values'] = Crypt::encryptString(json_encode($value));
    }

    /**
     * Decrypt and get changes.
     */
    public function getChangesAttribute($value): ?array
    {
        if (empty($value)) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($value);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt changes in AuditLog', [
                'audit_log_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Encrypt and set changes.
     */
    public function setChangesAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['changes'] = null;
            return;
        }

        $this->attributes['changes'] = Crypt::encryptString(json_encode($value));
    }

    /**
     * Scope to filter by auditable model.
     */
    public function scopeForModel($query, $model)
    {
        return $query->where('auditable_type', get_class($model))
                    ->where('auditable_id', $model->id);
    }

    /**
     * Scope to filter by event type.
     */
    public function scopeEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter system actions.
     */
    public function scopeSystemActions($query)
    {
        return $query->where('user_type', 'system');
    }

    /**
     * Get a human-readable description of the audit.
     */
    public function getDescription(): string
    {
        $user = $this->user_type === 'system' 
            ? 'System' 
            : ($this->user_email ?? 'Unknown User');

        $action = match($this->event) {
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
            'restored' => 'restored',
            default => $this->event,
        };

        $modelName = class_basename($this->auditable_type);

        return "{$user} {$action} {$modelName} #{$this->auditable_id}";
    }

    /**
     * Get formatted changes for display.
     */
    public function getFormattedChanges(): array
    {
        $formatted = [];
        $changes = $this->changes ?? [];
        $oldValues = $this->old_values ?? [];

        foreach ($changes as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            
            $formatted[] = [
                'field' => $this->formatFieldName($field),
                'old' => $this->formatValue($oldValue),
                'new' => $this->formatValue($newValue),
            ];
        }

        return $formatted;
    }

    /**
     * Format field name for display.
     */
    protected function formatFieldName(string $field): string
    {
        return ucwords(str_replace('_', ' ', $field));
    }

    /**
     * Format value for display.
     */
    protected function formatValue($value): string
    {
        if ($value === null) {
            return '(empty)';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        if (strlen((string)$value) > 100) {
            return substr((string)$value, 0, 100) . '...';
        }

        return (string)$value;
    }
}
