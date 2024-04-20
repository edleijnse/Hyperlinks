<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0cdcc2b516672200db353b34ee6a0f58
{
    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'chillerlan\\Settings\\' => 20,
            'chillerlan\\QRCode\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'chillerlan\\Settings\\' => 
        array (
            0 => __DIR__ . '/..' . '/chillerlan/php-settings-container/src',
        ),
        'chillerlan\\QRCode\\' => 
        array (
            0 => __DIR__ . '/..' . '/chillerlan/php-qrcode/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0cdcc2b516672200db353b34ee6a0f58::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0cdcc2b516672200db353b34ee6a0f58::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0cdcc2b516672200db353b34ee6a0f58::$classMap;

        }, null, ClassLoader::class);
    }
}
