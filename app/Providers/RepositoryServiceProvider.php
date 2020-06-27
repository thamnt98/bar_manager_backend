<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/27/19
 * Time: 6:45 PM
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Repositories\User\UserRepository',
            'App\Repositories\User\UserEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\RegisterMail\RegisterMailRepository',
            'App\Repositories\RegisterMail\RegisterMailEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\Bar\BarRepository',
            'App\Repositories\Bar\BarEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\Job\JobRepository',
            'App\Repositories\Job\JobEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\AccountLimitPlan\AccountLimitPlanRepository',
            'App\Repositories\AccountLimitPlan\AccountLimitPlanEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\CustomerSetting\CustomerSettingRepository',
            'App\Repositories\CustomerSetting\CustomerSettingEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\Customer\CustomerRepository',
            'App\Repositories\Customer\CustomerEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\Bottle\BottleRepository',
            'App\Repositories\Bottle\BottleEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\KeepBottle\KeepBottleRepository',
            'App\Repositories\KeepBottle\KeepBottleEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\OrderHistory\OrderHistoryRepository',
            'App\Repositories\OrderHistory\OrderHistoryEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\Cast\CastRepository',
            'App\Repositories\Cast\CastEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\PasswordReset\PasswordResetRepository',
            'App\Repositories\PasswordReset\PasswordResetEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\BottleCategory\BottleCategoryRepository',
            'App\Repositories\BottleCategory\BottleCategoryEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\DebitHistory\DebitHistoryRepository',
            'App\Repositories\DebitHistory\DebitHistoryEloquentRepository'
        );
    }
}
