<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Content\Page;
use App\Cms\Chrome\SiteContent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tanpa slug, profil membuka topik pertama sesuai urutan yang diatur admin
     * supaya tautan "Profil" di menu tetap punya isi.
     */
    public function index(): View
    {
        $pages = $this->halamanProfil();

        abort_if($pages->isEmpty(), 404);

        return $this->tampilkan($pages, $pages->first());
    }

    public function show(string $slug): View
    {
        $pages = $this->halamanProfil();

        $active = $pages->firstWhere('slug', $slug);

        abort_if($active === null, 404);

        return $this->tampilkan($pages, $active);
    }

    private function halamanProfil(): Collection
    {
        return Page::query()
            ->published()
            ->orderBy('sort_order')
            ->get();
    }

    private function tampilkan(Collection $pages, Page $active): View
    {
        return view('site.profile.show', [
            'pages' => $pages,
            'active' => $active,
            'banner' => SiteContent::section('profil.banner'),
        ]);
    }
}
