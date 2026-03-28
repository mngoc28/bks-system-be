<?php

namespace App\Traits;

use App\Services\StationService;
use Exception;
use Illuminate\Support\Facades\Config;

trait GmoTrait
{
    public function settingConfig($id)
    {
        $this->clearGmoConfig();
        $stationSrv = app()->make(StationService::class);
        $station = $stationSrv->getStationManagement($id);
        if (!$station) {
            throw new Exception("no data found");
        }
        Config::set("gmo-payment-gateway.creds.shop_id", $station->gmo_shop_id);
        Config::set(
            "gmo-payment-gateway.creds.shop_pass",
            $station->gmo_shop_pass
        );
        // Config::set('gmo-payment-gateway.creds.site_id', $station->gmo_site_id);
        // Config::set('gmo-payment-gateway.creds.site_pass', $station->gmo_site_pass);
        // Config::set('gmo-payment-gateway.secure.3dversion', $station->gmo_3ds_version);
        // Config::set('gmo-payment-gateway.timeout', $station->gmo_api_timeout);
        // Config::set('gmo-payment-gateway.public_key', $station->gmo_public_key);
        // Config::set('gmo-payment-gateway.key_hash', $station->gmo_public_key_hash);
    }

    public function clearGmoConfig()
    {
        Config::set("gmo-payment-gateway.creds.shop_id", "");
        Config::set("gmo-payment-gateway.creds.shop_pass", "");
        // Config::set('gmo-payment-gateway.creds.site_id', '');
        // Config::set('gmo-payment-gateway.creds.site_pass', '');
        // Config::set('gmo-payment-gateway.secure.3dversion', '');
        // Config::set('gmo-payment-gateway.timeout', '');
        // Config::set('gmo-payment-gateway.public_key', '');
        // Config::set('gmo-payment-gateway.key_hash', '');
    }
}
