<?php

namespace App\Filament\Admin\Resources\Concerns;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;

trait ScopesEditorOwnership
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user instanceof User && $user->hasRole('editor')) {
            $query->where('created_by', $user->getKey());
        }

        return $query;
    }
}
