<?php

namespace App\Cms\Content;

use App\Models\Content\Innovation;
use App\Models\Content\Publication;
use App\Models\Content\Researcher;
use App\Models\Content\ResearchProject;
use Illuminate\Support\Facades\Cache;

final class PublicStats
{
    public static function snapshot(): array
    {
        return Cache::remember('stemc.public_stats', 300, fn () => [
            'researchers' => Researcher::query()->published()->count(),
            'publications' => Publication::query()->published()->count(),
            'projects' => ResearchProject::query()->published()->where('status', 'berjalan')->count(),
            'hki' => Innovation::query()->published()->hki()->count(),
        ]);
    }
}
