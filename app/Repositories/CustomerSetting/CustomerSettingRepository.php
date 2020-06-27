<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/31/19
 * Time: 10:03 AM
 */

namespace App\Repositories\CustomerSetting;


interface CustomerSettingRepository
{
    public function findByBarId($barId);

    public function findByBar($bar);

    public function updateCustomerSetting($barIds, $input);

    public function getCustomerSettingByUser($barIds);

}