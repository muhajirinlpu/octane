<?php

namespace Laravel\Octane;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;
use Laravel\Octane\Events\RequestHandled;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Facades\Octane;
use Symfony\Component\HttpFoundation\Response;

class ApplicationGateway
{
    use DispatchesEvents;

    public function __construct(protected Application $app, protected Application $sandbox)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request): Response
    {
        $request->enableHttpMethodParameterOverride();

//        $loggable = [
//            'pcid' => \Co::getCid(),
//            'app' => spl_object_id($this->app),
//            'sandbox' => spl_object_id($this->sandbox),
//            'request' => $request->url(),
//        ];
//
//        Log::info('[coro] handle '. json_encode($loggable));

        $this->dispatchEvent($this->sandbox, new RequestReceived($this->app, $this->sandbox, $request));

        if (Octane::hasRouteFor($request->getMethod(), '/'.$request->path())) {
            return Octane::invokeRoute($request, $request->getMethod(), '/'.$request->path());
        }

        return tap($this->sandbox->make(Kernel::class)->handle($request), function ($response) use ($request) {
            $this->dispatchEvent($this->sandbox, new RequestHandled($this->sandbox, $request, $response));
        });
    }

    /**
     * "Shut down" the application after a request.
     */
    public function terminate(Request $request, Response $response): void
    {
        $this->sandbox->make(Kernel::class)->terminate($request, $response);

        $this->dispatchEvent($this->sandbox, new RequestTerminated($this->app, $this->sandbox, $request, $response));

        $route = $request->route();

        if ($route instanceof Route && method_exists($route, 'flushController')) {
            $route->flushController();
        }
    }
}
