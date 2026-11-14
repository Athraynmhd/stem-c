<?php

namespace App\Http\Controllers\Site\Concerns;

use App\Cms\Chrome\SiteContent;

trait LoadsPageChrome
{
    /**
     * @return array{banner: ?\App\Models\Chrome\SiteSection, intro: ?\App\Models\Chrome\SiteSection}
     */
    protected function pageChrome(string $page): array
    {
        return [
            'banner' => SiteContent::section("{$page}.banner"),
            'intro' => SiteContent::section("{$page}.intro"),
        ];
    }
}
