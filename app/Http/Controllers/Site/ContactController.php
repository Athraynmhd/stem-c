<?php

namespace App\Http\Controllers\Site;

use App\Cms\Security\MathCaptcha;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Chrome\Setting;
use App\Models\Contact\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    use LoadsPageChrome;

    public function index(): View
    {
        return $this->formView('kontak', 'umum');
    }

    public function partnership(): View
    {
        return $this->formView('kemitraan', 'kolaborasi');
    }

    public function store(Request $request): RedirectResponse
    {
        $sukses = 'Pesan Anda telah terkirim. Tim STEM-C akan menindaklanjuti.';

        if (filled($request->input('company_url'))) {
            return back()->with('success', $sukses);
        }

        if (! MathCaptcha::check($request->input('captcha'))) {
            return back()->withInput()->withErrors(['captcha' => 'Jawaban verifikasi tidak tepat.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['nullable', 'string', 'max:190'],
            'intent' => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:5000'],
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Persetujuan pemrosesan data wajib dicentang.',
        ]);

        ContactMessage::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'] ?? null,
            'intent' => $data['intent'] ?? 'umum',
            'message' => $data['message'],
            'consent' => true,
            'status' => 'new',
        ]);

        return back()->with('success', $sukses);
    }

    private function formView(string $chrome, string $intent): View
    {
        return view('site.contact.index', [
            'email' => Setting::get('email', 'stemc@usk.ac.id'),
            'address' => Setting::get('address', 'Lab Terpadu, Universitas Syiah Kuala'),
            'phone' => Setting::get('phone'),
            'mapEmbed' => Setting::get('map_embed'),
            'captchaQuestion' => MathCaptcha::question(),
            'intent' => $intent,
            ...$this->pageChrome($chrome),
        ]);
    }
}
