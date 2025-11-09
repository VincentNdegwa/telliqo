<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->auditCreated();
        });

        static::updated(function ($model) {
            $model->auditUpdated();
        });

        static::deleted(function ($model) {
            $model->auditDeleted();
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->auditRestored();
            });
        }
    }

    /**
     * Get all audit logs for this model.
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }

    /**
     * Audit the creation event.
     */
    protected function auditCreated()
    {
        $this->createAuditLog('created', [], $this->getAuditableAttributes());
    }

    /**
     * Audit the update event.
     */
    protected function auditUpdated()
    {
        $changes = $this->getChanges();
        $original = $this->getOriginal();

        // Filter out irrelevant changes (timestamps, etc.)
        $changes = $this->filterAuditableChanges($changes);

        if (empty($changes)) {
            return; // No significant changes to audit
        }

        $oldValues = [];
        $newValues = [];

        foreach ($changes as $key => $newValue) {
            $oldValues[$key] = $original[$key] ?? null;
            $newValues[$key] = $newValue;
        }

        $this->createAuditLog('updated', $oldValues, $newValues, $changes);
    }

    /**
     * Audit the deletion event.
     */
    protected function auditDeleted()
    {
        $this->createAuditLog('deleted', $this->getAuditableAttributes(), []);
    }

    /**
     * Audit the restoration event.
     */
    protected function auditRestored()
    {
        $this->createAuditLog('restored', [], $this->getAuditableAttributes());
    }

    /**
     * Create an audit log entry.
     */
    protected function createAuditLog(
        string $event,
        array $oldValues = [],
        array $newValues = [],
        array $changes = []
    ): void {
        $user = Auth::user();
        $isSystem = $this->isSystemAction();

        AuditLog::create([
            'user_id' => $isSystem ? null : ($user->id ?? null),
            'user_type' => $isSystem ? 'system' : 'user',
            'user_email' => $isSystem ? null : ($user->email ?? null),
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'event' => $event,
            'ip_address' => $this->getIpAddress(),
            'user_agent' => Request::header('User-Agent'),
            'old_values' => $this->sanitizeAuditData($oldValues),
            'new_values' => $this->sanitizeAuditData($newValues),
            'changes' => $this->sanitizeAuditData($changes),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'additional_data' => $this->getAdditionalAuditData(),
            'tags' => $this->getAuditTags($event),
        ]);
    }

    /**
     * Get auditable attributes (filtered).
     */
    protected function getAuditableAttributes(): array
    {
        $attributes = $this->getAttributes();
        return $this->filterAuditableChanges($attributes);
    }

    /**
     * Filter out non-auditable changes.
     */
    protected function filterAuditableChanges(array $changes): array
    {
        $excluded = $this->getAuditExclude();
        
        return array_filter($changes, function ($key) use ($excluded) {
            return !in_array($key, $excluded);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get attributes to exclude from audit.
     */
    protected function getAuditExclude(): array
    {
        return array_merge(
            ['updated_at', 'created_at', 'deleted_at', 'remember_token'],
            $this->auditExclude ?? []
        );
    }

    /**
     * Sanitize audit data (remove sensitive fields).
     */
    protected function sanitizeAuditData(array $data): array
    {
        $sensitive = $this->getAuditSensitiveFields();
        
        foreach ($sensitive as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Get sensitive fields to redact in audit logs.
     */
    protected function getAuditSensitiveFields(): array
    {
        return array_merge(
            ['password', 'password_confirmation', 'current_password'],
            $this->auditSensitive ?? []
        );
    }

    /**
     * Check if current action is system-initiated.
     */
    protected function isSystemAction(): bool
    {
        // Check if running in console (background job, command, etc.)
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return true;
        }

        // Check if no authenticated user
        if (!Auth::check()) {
            return true;
        }

        // Allow models to define custom system action logic
        if (method_exists($this, 'isCustomSystemAction')) {
            return $this->isCustomSystemAction();
        }

        return false;
    }

    /**
     * Get IP address for audit.
     */
    protected function getIpAddress(): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        return Request::ip();
    }

    /**
     * Get additional audit data (can be overridden in models).
     */
    protected function getAdditionalAuditData(): ?array
    {
        if (method_exists($this, 'customAuditData')) {
            return $this->customAuditData();
        }

        return null;
    }

    /**
     * Get tags for the audit log (can be overridden in models).
     */
    protected function getAuditTags(string $event): ?string
    {
        $tags = [];

        // Add model name as tag
        $tags[] = class_basename($this);

        // Add event as tag
        $tags[] = $event;

        // Allow models to add custom tags
        if (method_exists($this, 'customAuditTags')) {
            $customTags = $this->customAuditTags($event);
            if (is_array($customTags)) {
                $tags = array_merge($tags, $customTags);
            }
        }

        return implode(',', $tags);
    }

    /**
     * Get audit history with detailed information.
     */
    public function getAuditHistory(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->auditLogs()->limit($limit)->get();
    }

    /**
     * Get specific audit event.
     */
    public function getAuditEvent(string $event)
    {
        return $this->auditLogs()->where('event', $event)->get();
    }

    /**
     * Check if model has been audited.
     */
    public function hasAuditLogs(): bool
    {
        return $this->auditLogs()->exists();
    }
}
