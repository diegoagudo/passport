<?php

namespace DiegoAgudo\Passport\Tests\Feature;

use Illuminate\Contracts\Routing\Registrar;
use DiegoAgudo\Passport\Client;
use DiegoAgudo\Passport\Http\Middleware\CheckClientCredentials;
use DiegoAgudo\Passport\Http\Middleware\CheckClientCredentialsForAnyScope;
use DiegoAgudo\Passport\Passport;
use Orchestra\Testbench\TestCase;

class ActingAsClientTest extends TestCase
{
    public function testActingAsClientWhenTheRouteIsProtectedByCheckClientCredentialsMiddleware()
    {
        $this->withoutExceptionHandling();

        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);

        $router->get('/foo', function () {
            return 'bar';
        })->middleware(CheckClientCredentials::class);

        Passport::actingAsClient(new Client());

        $response = $this->get('/foo');
        $response->assertSuccessful();
        $response->assertSee('bar');
    }

    public function testActingAsClientWhenTheRouteIsProtectedByCheckClientCredentialsForAnyScope()
    {
        $this->withoutExceptionHandling();

        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);

        $router->get('/foo', function () {
            return 'bar';
        })->middleware(CheckClientCredentialsForAnyScope::class.':testFoo');

        Passport::actingAsClient(new Client(), ['testFoo']);

        $response = $this->get('/foo');
        $response->assertSuccessful();
        $response->assertSee('bar');
    }
}
