<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9861168e16b9e713d0afe50ea9c6b7ce
{
    public static $files = array (
        '33197a0023ced5fbf8f861d1c4ca048d' => __DIR__ . '/..' . '/topthink/think-orm/src/config.php',
    );

    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-orm/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9861168e16b9e713d0afe50ea9c6b7ce::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9861168e16b9e713d0afe50ea9c6b7ce::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}