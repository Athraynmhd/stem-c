<?php

namespace App\Cms\Security;

/**
 * Captcha aritmetika sederhana di sesi — tanpa layanan pihak ketiga.
 * Cukup untuk menghentikan bot form kontak yang sudah lolos honeypot.
 */
final class MathCaptcha
{
    public const SESSION_KEY = 'stemc_math_captcha';

    /**
     * @return array{question: string, answer: int}
     */
    public static function generate(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $answer = $a + $b;

        session([self::SESSION_KEY => $answer]);

        return [
            'question' => "{$a} + {$b}",
            'answer' => $answer,
        ];
    }

    public static function check(?string $input): bool
    {
        $expected = session(self::SESSION_KEY);

        if ($expected === null || $input === null || $input === '') {
            return false;
        }

        $ok = (int) $input === (int) $expected;
        session()->forget(self::SESSION_KEY);

        return $ok;
    }

    public static function question(): string
    {
        return self::generate()['question'];
    }
}
