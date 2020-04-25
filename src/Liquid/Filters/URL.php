<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use Illuminate\Support\Facades\Config;

class URL
{
    /**
     * Return asset URL
     *
     * @param string $input
     *
     * @return string
     */
    public static function asset_url($input)
    {
        return Config::get('app.asset_url') . $input;
    }

}