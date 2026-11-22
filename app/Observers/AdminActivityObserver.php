<?php

namespace App\Observers;

use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Records create/update/delete of catalog and config models done from the admin area.
 * Only changed field names are stored, never values (products hold encrypted credentials).
 */
class AdminActivityObserver
{
    public function __construct(private ActivityLogger $activityLogger) {}

    public function created(Model $model): void
    {
        $this->record($model, 'created', 'Tạo');
    }

    public function updated(Model $model): void
    {
        $changed = array_values(array_diff(array_keys($model->getChanges()), ['updated_at']));

        if ($changed !== []) {
            $this->record($model, 'updated', 'Cập nhật', ['changed' => $changed]);
        }
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', 'Xóa');
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function record(Model $model, string $event, string $verb, array $properties = []): void
    {
        if (! request()->routeIs('admin.*') || auth()->user()?->isStaff() !== true) {
            return;
        }

        $name = class_basename($model);
        $label = $model->getAttribute('name') ?? $model->getAttribute('title') ?? $model->getAttribute('code') ?? '#'.$model->getKey();

        $this->activityLogger->log(
            strtolower($name).'.'.$event,
            $verb.' '.$name.': '.$label,
            $model,
            $properties,
        );
    }
}
