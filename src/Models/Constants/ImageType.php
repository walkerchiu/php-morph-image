<?php

namespace WalkerChiu\MorphImage\Models\Constants;

/**
 * @license MIT
 * @package WalkerChiu\MorphImage
 *
 *
 */

class ImageType
{
    public static function getCodes()
    {
        $items = [];
        $types = self::all();
        foreach ($types as $code=>$type) {
            array_push($items, $code);
        }

        return $items;
    }

    public static function options($only_vaild = false)
    {
        $items = $only_vaild ? [] : ['' => trans('php-core::system.null')];

        $types = self::all();
        foreach ($types as $key=>$value) {
            $items = array_merge($items, [$key => trans('php-morph-image::system.imageType.'.$key)]);
        }

        return $items;
    }

    public static function all()
    {
        return [
            'cover' => 'Cover',
            'icon'  => 'Icon',
            'image' => 'Image',
            'logo'  => 'Logo',
            'frame' => 'Frame',

            'eye'         => 'Eye',
            'face'        => 'Face',
            'fingerprint' => 'Fingerprint',
            'imprint'     => 'Imprint',
            'palm'        => 'Palm',
            'object'      => 'Object'
        ];
    }
}
