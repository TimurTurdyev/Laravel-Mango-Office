<?php
namespace TimurTurdyev\MangoOffice;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
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