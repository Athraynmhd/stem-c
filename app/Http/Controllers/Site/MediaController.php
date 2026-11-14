<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Content\MediaItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    use LoadsPageChrome;

    public function index(Request $request): View
    {
        $album = $request->query('album');
        $type = in_array($request->query('jenis'), ['photo', 'video'], true)
            ? $request->query('jenis')
            : null;

        $query = MediaItem::query()->published();

        if ($album) {
            $query->where('album', $album);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $items = $query
            ->orderBy('sort_order')
            ->latest('captured_at')
            ->paginate(12)
            ->withQueryString();

        return view('site.media.index', [
            'items' => $items,
            'albums' => MediaItem::query()
                ->published()
                ->whereNotNull('album')
                ->distinct()
                ->orderBy('album')
                ->pluck('album'),
            'activeAlbum' => $album,
            'activeType' => $type,
            ...$this->pageChrome('dokumentasi'),
        ]);
    }
}
