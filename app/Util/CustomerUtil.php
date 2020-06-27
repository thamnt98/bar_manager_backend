<?php

namespace App\Util;

class CustomerUtil
{
    public function getUrlIcon($path)
    {
        return is_null($path) ? null : config('constant.amazon_web_service_domain') . config('constant.folder_avatar_customer_s3') . '/' . $path;
    }
}
