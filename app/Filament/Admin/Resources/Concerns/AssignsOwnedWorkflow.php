<?php

namespace App\Filament\Admin\Resources\Concerns;

use App\Cms\Access\CmsAccess;

trait AssignsOwnedWorkflow
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['workflow_status'] = $this->sanitizeWorkflow($data['workflow_status'] ?? CmsAccess::WORKFLOW_DRAFT);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('workflow_status', $data)) {
            $data['workflow_status'] = $this->sanitizeWorkflow($data['workflow_status']);
        }

        return $data;
    }

    private function sanitizeWorkflow(?string $status): string
    {
        $allowed = array_keys(CmsAccess::workflowOptions(auth()->user()));

        if ($status === null || ! in_array($status, $allowed, true)) {
            return CmsAccess::WORKFLOW_DRAFT;
        }

        return $status;
    }
}
