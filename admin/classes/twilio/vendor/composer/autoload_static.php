<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit06d4c38864f1e66f9511be5b8946a1ac
{
    public static $files = array (
        'a0edc8309cc5e1d60e3047b5df6b7052' => __DIR__ . '/..' . '/guzzlehttp/psr7/src/functions_include.php',
        'c964ee0ededf28c96ebd9db5099ef910' => __DIR__ . '/..' . '/guzzlehttp/promises/src/functions_include.php',
        '7ba3c774c30c8399e359b5ff7f3b943e' => __DIR__ . '/..' . '/tightenco/collect/src/Illuminate/Support/helpers.php',
        '37a3dc5111fe8f707ab4c132ef1dbc62' => __DIR__ . '/..' . '/guzzlehttp/guzzle/src/functions_include.php',
    );

    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Twilio\\' => 7,
        ),
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
        ),
        'K' => 
        array (
            'Kunnu\\Dropbox\\' => 14,
        ),
        'I' => 
        array (
            'Illuminate\\' => 11,
        ),
        'G' => 
        array (
            'GuzzleHttp\\Psr7\\' => 16,
            'GuzzleHttp\\Promise\\' => 19,
            'GuzzleHttp\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Twilio\\' => 
        array (
            0 => __DIR__ . '/..' . '/twilio/sdk/Twilio',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Kunnu\\Dropbox\\' => 
        array (
            0 => __DIR__ . '/..' . '/kunalvarma05/dropbox-php-sdk/src/Dropbox',
        ),
        'Illuminate\\' => 
        array (
            0 => __DIR__ . '/..' . '/tightenco/collect/src/Illuminate',
        ),
        'GuzzleHttp\\Psr7\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/psr7/src',
        ),
        'GuzzleHttp\\Promise\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/promises/src',
        ),
        'GuzzleHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/guzzle/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit06d4c38864f1e66f9511be5b8946a1ac::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit06d4c38864f1e66f9511be5b8946a1ac::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
