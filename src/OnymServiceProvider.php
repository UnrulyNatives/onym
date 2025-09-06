<?php

namespace Blaspsoft\Onym;

use Illuminate\Support\ServiceProvider;
use Blaspsoft\Onym\Strategies\RandomStrategy;
use Blaspsoft\Onym\Strategies\UuidStrategy;
use Blaspsoft\Onym\Strategies\TimestampStrategy;
use Blaspsoft\Onym\Strategies\DateStrategy;
use Blaspsoft\Onym\Strategies\NumberedStrategy;
use Blaspsoft\Onym\Strategies\SlugStrategy;
use Blaspsoft\Onym\Strategies\HashStrategy;

class OnymServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('onym.php'),
            ], 'onym-config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'onym');

        // Register the strategy registry as a singleton
        $this->app->singleton(StrategyRegistry::class, function () {
            $registry = new StrategyRegistry();
            
            // Register all default strategies
            $registry->registerMany([
                new RandomStrategy(),
                new UuidStrategy(),
                new TimestampStrategy(),
                new DateStrategy(),
                new NumberedStrategy(),
                new SlugStrategy(),
                new HashStrategy(),
            ]);
            
            return $registry;
        });

        // Register the main class to use with the facade
        $this->app->bind('onym', function ($app) {
            return new Onym($app->make(StrategyRegistry::class));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'onym',
            StrategyRegistry::class,
        ];
    }
}