<?php
namespace TimurTurdyev\MangoOffice;

use \Illuminate\Support\ServiceProvider;

class MangoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/mangooffice.php' => config_path('mangooffice.php')]);
    }

    public function register()
    {
    }

}