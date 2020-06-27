<?php

namespace App\Providers;

use App\Validators\RestValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('pwd_not_special_character', 'App\Validators\PasswordValidator@special');
        Validator::extend('pwd_lowercase', 'App\Validators\PasswordValidator@lowercase');
        Validator::extend('pwd_uppercase', 'App\Validators\PasswordValidator@uppercase');
        Validator::extend('pwd_numeric', 'App\Validators\PasswordValidator@numeric');
        Validator::extend('pwd_start_with', 'App\Validators\PasswordValidator@startWith');
        Validator::extend('date_multi_format', 'App\Validators\DateFormat@multi_format');

        if(env('APP_DEBUG')) {
            DB::listen(function($query) {
                $bindingArr = Array();
                foreach ($query->bindings as $binding) {
                    $type = gettype($binding);
                    switch ($type) {
                        case "object":
                            $class = get_class($binding);
                            switch ($class) {
                                case "DateTime":
                                    $bindingArr[] = $binding->format('Y-m-d H:i:s');
                                    break;
                                default:
                                    throw new \Exception("Unexpected binding argument class ($class)");
                            }
                            break;
                        default:
                            $bindingArr[] = $binding;
                    }
                }
                File::append(
                    storage_path('/logs/query.log'),
                    $query->sql . ' [' . implode(', ', $bindingArr) . ']' . PHP_EOL
                );
            });
        }
    }
}
