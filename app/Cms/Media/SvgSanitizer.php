<?php

namespace App\Cms\Media;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Membersihkan SVG yang diunggah lewat dashboard sebelum disimpan ke disk publik.
 *
 * SVG boleh memuat skrip dan disajikan dari origin yang sama, jadi berkas
 * mentah dari admin tetap berisiko XSS tersimpan. Jalur inline menolak SVG
 * sama sekali; jalur panel memakai kelas ini agar logo SVG tetap bisa diganti.
 */
final class SvgSanitizer
{
    /** @var list<string> */
    private const TAGS_DITOLAK = [
        'script',
        'foreignObject',
        'foreignobject',
        'iframe',
        'embed',
        'object',
        'audio',
        'video',
        'link',
        'meta',
        'base',
        'handler',
        'set',
    ];

    public static function isSvg(UploadedFile $file): bool
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $mime = strtolower((string) $file->getMimeType());

        if (in_array($mime, ['image/svg+xml', 'image/svg'], true)) {
            return true;
        }

        if ($ext !== 'svg') {
            return false;
        }

        $head = (string) @file_get_contents((string) $file->getRealPath(), false, null, 0, 2048);

        return str_contains(strtolower($head), '<svg');
    }

    public static function cleanFile(UploadedFile $file): string
    {
        $raw = (string) file_get_contents((string) $file->getRealPath());

        return self::clean($raw);
    }

    public static function clean(string $svg): string
    {
        $svg = trim($svg);

        if ($svg === '' || ! preg_match('/<svg\b/i', $svg)) {
            throw new RuntimeException('Berkas SVG tidak valid.');
        }

        if (preg_match('/<\?php/i', $svg)) {
            throw new RuntimeException('Berkas SVG mengandung konten yang tidak diizinkan.');
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadXML($svg, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (! $loaded) {
            throw new RuntimeException('Berkas SVG tidak dapat dibaca.');
        }

        $root = $document->documentElement;

        if (! $root instanceof DOMElement || strtolower($root->tagName) !== 'svg') {
            throw new RuntimeException('Akar berkas harus elemen <svg>.');
        }

        self::bersihkanPohon($document, $root);

        $output = $document->saveXML($root);

        if (! is_string($output) || $output === '') {
            throw new RuntimeException('Gagal menulis ulang SVG yang sudah dibersihkan.');
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".$output;
    }

    private static function bersihkanPohon(DOMDocument $document, DOMElement $root): void
    {
        $xpath = new DOMXPath($document);

        foreach (self::TAGS_DITOLAK as $tag) {
            /** @var \DOMNodeList<\DOMNode> $nodes */
            $nodes = $xpath->query('//*[local-name()="'.$tag.'"]');

            if ($nodes === false) {
                continue;
            }

            $hapus = [];
            foreach ($nodes as $node) {
                $hapus[] = $node;
            }

            foreach ($hapus as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        /** @var \DOMNodeList<\DOMNode> $semua */
        $semua = $xpath->query('//*');

        if ($semua === false) {
            return;
        }

        foreach ($semua as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            self::bersihkanAtribut($node);
        }

        // Instruction stylesheet eksternal / skrip tersembunyi.
        $instruksi = [];

        for ($child = $document->firstChild; $child instanceof DOMNode; $child = $child->nextSibling) {
            if ($child->nodeType === XML_PI_NODE) {
                $instruksi[] = $child;
            }
        }

        foreach ($instruksi as $node) {
            $document->removeChild($node);
        }
    }

    private static function bersihkanAtribut(DOMElement $element): void
    {
        $hapus = [];

        foreach ($element->attributes ?? [] as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim((string) $attribute->value);

            if (str_starts_with($name, 'on')) {
                $hapus[] = $attribute->name;
                continue;
            }

            if (in_array($name, ['href', 'xlink:href', 'src'], true) || str_ends_with($name, ':href')) {
                $lower = strtolower($value);

                if (
                    str_starts_with($lower, 'javascript:')
                    || str_starts_with($lower, 'data:text/html')
                    || str_starts_with($lower, 'data:image/svg')
                    || preg_match('#^(https?:)?//#i', $value)
                ) {
                    $hapus[] = $attribute->name;
                }
            }

            if ($name === 'style' && preg_match('/expression\s*\(|javascript\s*:/i', $value)) {
                $hapus[] = $attribute->name;
            }
        }

        foreach ($hapus as $name) {
            $element->removeAttribute($name);
        }
    }
}
