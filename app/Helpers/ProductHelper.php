<?php

namespace App\Helpers;

use App\Models\Product;

class ProductHelper
{
    public static function createSlug(string $name): ?string {
        $name = preg_replace('/\s+/', '', strtolower($name));
        $new_name = $name . self::generateRandomString();
        return hash('sha1', $new_name);
    }

    public static function createEAN(): ?string {
        $code = '200' . str_pad(mt_rand(100000000, 999999999), 9, '0');
        $weightflag = true;
        $sum = 0;
        // Weight for a digit in the checksum is 3, 1, 3.. starting from the last digit.
        // loop backwards to make the loop length-agnostic. The same basic functionality
        // will work for codes of different lengths.
        for ($i = strlen($code) - 1; $i >= 0; $i--)
        {
            $sum += (int)$code[$i] * ($weightflag?3:1);
            $weightflag = !$weightflag;
        }
        $code .= (10 - ($sum % 10)) % 10;
        return $code;
    }

    public static function checkSlugExists(string $slug): ?bool
    {
        $count = Product::where('slug', $slug)->count();
        return $count > 0;
    }

    public static function checkEANExists(string $ean): ?bool
    {
        $count = Product::where('ean', $ean)->count();
        return $count > 0;
    }

    private static function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}

