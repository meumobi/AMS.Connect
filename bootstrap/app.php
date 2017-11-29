<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

/*
    See https://nicksilvestro.net/2016/05/28/adding-laravels-storage-facade-into-lumen/
*/
$app->configure('filesystems');
$app->withFacades();
class_alias('Illuminate\Support\Facades\Storage', 'Storage');

// $app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
// ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

$app->register(Illuminate\Filesystem\FilesystemServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    require __DIR__.'/../routes/web.php';
});

/*
    Logging on stderr to aggregate logs on heroku
    https://devcenter.heroku.com/articles/php-logging
*/

$app->configureMonologUsing(function($monolog) {
    switch (env('LOG_CHANNEL')) {
        case 'local':
            $monolog->pushHandler(new \Monolog\Handler\StreamHandler('php://stderr',env("LOG_LEVEL")));
            break;  
        case 'slack':      
            $url = env('SLACK_URL');
            $channel = env('SLACK_CHANNEL');
            $username = env('SLACK_USERNAME');
            $monolog->pushHandler(new \Monolog\Handler\SlackWebhookHandler(
                $url,
                $channel,
                $username,
                false,
                null,
                false,
                false,
                env("LOG_LEVEL")
            ));
            break;
    }
    return $monolog;
});


/*
    See https://github.com/Niellles/lumen-commands
*/
if (env('APP_ENV') === 'local') {
    $app->bind(Illuminate\Database\ConnectionResolverInterface::class, Illuminate\Database\ConnectionResolver::class);
    $app->register(Niellles\LumenCommands\LumenCommandsServiceProvider::class);
}

return $app;
