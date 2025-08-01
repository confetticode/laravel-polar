<?php

namespace ConfettiCode\LaravelPolar;

use ConfettiCode\LaravelPolar\Commands\ListProductsCommand;
use ConfettiCode\LaravelPolar\View\Components\Button;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelPolarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-polar')
            ->hasConfigFile(["polar", "webhook-client"])
            ->hasViews()
            ->hasViewComponent('polar', Button::class)
            ->hasMigrations()
            ->discoversMigrations()
            ->hasRoute("web")
            ->hasCommands(
                ListProductsCommand::class,
            )
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishAssets()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->copyAndRegisterServiceProviderInApp();
            });
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(Polar::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->bootDirectives();
    }

    protected function bootDirectives(): void
    {
        Blade::directive('polarEmbedScript', function () {
            return "<?php echo view('polar::js'); ?>";
        });
    }
}
