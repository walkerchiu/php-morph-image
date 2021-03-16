<?php

namespace WalkerChiu\MorphImage\Models\Entities;

use WalkerChiu\Core\Models\Entities\Lang;

class ImageLang extends Lang
{
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        $this->table = config('wk-core.table.morph-image.images_lang');

        parent::__construct($attributes);
    }
}
