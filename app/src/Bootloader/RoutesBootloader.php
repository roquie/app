<?php

declare(strict_types=1);

namespace App\Bootloader;

use App\Middleware\LocaleSelector;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Bootloader\Http\RoutesBootloader as BaseRoutesBootloader;
use Spiral\Cookies\Middleware\CookiesMiddleware;
use Spiral\Csrf\Middleware\CsrfMiddleware;
use Spiral\Debug\StateCollector\HttpCollector;
use Spiral\Http\Middleware\ErrorHandlerMiddleware;
use Spiral\Http\Middleware\JsonPayloadMiddleware;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;
use Spiral\Session\Middleware\SessionMiddleware;

final class RoutesBootloader extends BaseRoutesBootloader
{
    public function __construct(
        private readonly DirectoriesInterface $dirs
    ) {
    }

    protected function globalMiddleware(): array
    {
        return [
            ErrorHandlerMiddleware::class,
            JsonPayloadMiddleware::class,
            HttpCollector::class,
            LocaleSelector::class,
        ];
    }

    protected function middlewareGroups(): array
    {
        return [
            'web' => [
                CookiesMiddleware::class,
                SessionMiddleware::class,
                CsrfMiddleware::class,
            ],
            'api' => [
                //
            ]
        ];
    }

    protected function defineRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->dirs->get('app') . '/routes/web.php')->group('web');
        $routes->import($this->dirs->get('app') . '/routes/api.php')->group('api');

        $routes->default('/[<controller>[/<action>]]')
            ->namespaced('App\\Controller')
            ->defaults([
                'controller' => 'home',
                'action' => 'index',
            ])
            ->middleware([
                CookiesMiddleware::class,
                SessionMiddleware::class,
                CsrfMiddleware::class,
            ]);
    }
}
