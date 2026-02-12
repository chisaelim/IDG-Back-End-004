<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnicodeCorrection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $data = $request->all();

        $data = $this->replaceUnicodeRecursive($data);

        $request->replace($data);

        return $next($request);
    }

    private function replaceUnicodeRecursive($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replaceUnicodeRecursive($value);
            }
        } else {
            $data = is_string($data) ? $this->replaceUnicode(preg_replace('/[ \t\x0B\f\x{A0}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}]+/u', ' ', $data)) : $data;
        }
        return $data;
    }
    private function replaceUnicode($text)
    {
        $salabpi = ['ង', 'ញ', 'ប', 'ម', 'យ', 'រ', 'វ'];
        $treysab = ['ស', 'ហ', 'អ'];
        $chars = array_merge($salabpi, $treysab);
        $vowels = ['ិ', 'ី', 'ឹ', 'ឺ', 'ើ'];

        // Direct replacements
        $text = str_replace('ា' . 'ំ', 'ាំ', $text);
        $text = str_replace('េ' . 'ី', 'ើ', $text);
        $text = str_replace('េ' . 'ា', 'ោ', $text);
        $text = str_replace('េ' . 'ះ', 'េះ', $text);
        $text = str_replace('ោ' . 'ះ', 'ោះ', $text);
        $text = str_replace('េ' . 'ុ' . 'ី', 'ុ' . 'ើ', $text);

        foreach ($chars as $char) {
            foreach ($vowels as $vowel) {
                if (in_array($char, $salabpi)) {
                    $replacementSign = '៉';
                } elseif (in_array($char, $treysab)) {
                    $replacementSign = '៊';
                } else {
                    continue;
                }
                $word = $char . 'ុ' . $vowel;
                $replacement = $char . $replacementSign . $vowel;
                $text = str_replace($word, $replacement, $text);
            }
        }
        return $text;
    }
}
