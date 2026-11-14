<?php

namespace App\Models\Concerns;

use App\Cms\Access\CmsAccess;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;

trait HasWorkflow
{
    public function initializeHasWorkflow(): void
    {
        if (! in_array('workflow_status', $this->fillable, true)) {
            $this->fillable[] = 'workflow_status';
        }
    }

    public function scopePublished(Builder $query): Builder
    {
        if (in_array('workflow_status', $this->getFillable(), true) || $this->isFillable('workflow_status')) {
            return $query->where('workflow_status', CmsAccess::WORKFLOW_PUBLISHED);
        }

        if (in_array('status', $this->getFillable(), true)) {
            return $query->where('status', CmsAccess::WORKFLOW_PUBLISHED);
        }

        return $query->where('is_published', true);
    }

    public static function bootHasWorkflow(): void
    {
        static::saving(function ($model): void {
            $user = auth()->user();
            $status = $model->getAttribute('workflow_status');

            if ($status === null && $model->isFillable('is_published')) {
                $status = $model->getAttribute('is_published')
                    ? CmsAccess::WORKFLOW_PUBLISHED
                    : CmsAccess::WORKFLOW_DRAFT;
                $model->setAttribute('workflow_status', $status);
            }

            if ($user instanceof User && $user->hasRole('editor') && $status === CmsAccess::WORKFLOW_PUBLISHED) {
                $status = CmsAccess::WORKFLOW_REVIEW;
                $model->setAttribute('workflow_status', $status);
            }

            if ($status !== null && $model->isFillable('is_published')) {
                $model->setAttribute('is_published', $status === CmsAccess::WORKFLOW_PUBLISHED);
            }
        });
    }
}
