<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use App\Helpers\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL as FacadesURL;

class URL
{
    /**
     * Return asset URL
     *
     * @param string $input
     *
     * @return string
     */
    public static function asset_url(string $input): string
    {
        return Config::get('app.asset_url') . $input;
    }

    /**
     * Return asset URL
     *
     * @param string $input
     *
     * @return string
     */
    public static function service_url(int $id): string
    {
        $slug = Helper::getCatalogItemSlug($id);
        return FacadesURL::route('service.show', [$slug]);
    }

}