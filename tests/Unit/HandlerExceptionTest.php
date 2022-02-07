<?php

namespace Tests\Unit;

use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class HandlerExceptionTest extends TestCase
{

    /**
     * @test
     */
    public function it_converts_an_exception_into_json_api_spec_error_response()
    {
        $handler = app(Handler::class);

        $request = Request::create('test');

        $request->headers->set('Accept', 'application/vnd.api+json');

        $exception = new \Exception('Test exception');

        $response = $handler->render($request, $exception);

        TestResponse::fromBaseResponse($response)->assertJson([
            'errors' => [
                [
                    'title' => 'Exception',
                    'details' => 'Test exception',
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_converts_HTTP_exception_into_json_api_spec_error_response()
    {
        $handler = app(Handler::class);

        $request = Request::create('test');

        $request->headers->set('Accept', 'application/vnd.api+json');

        $exception = new HttpException(404, 'Not found');

        $response = $handler->render($request, $exception);

        TestResponse::fromBaseResponse($response)->assertJson([
            'errors' => [
                [
                    'title' => 'Http Exception',
                    'details' => 'Not found',
                ]
            ]
        ])->assertNotFound();
    }

    /**
     * @test
     */
    public function it_converts_authentication_exception_into_json_api_spec_error_response()
    {

        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept', 'application/vnd.api+json');

        $exception = new AuthenticationException();

        $handler = app(Handler::class);

        $response = $handler->render($request, $exception);

        TestResponse::fromBaseResponse($response)->assertJson([
            'errors' => [
                [
                    'title' => 'Authentication Exception',
                    'details' => 'Unauthenticated.'
                ]
            ]
        ])->assertForbidden();

    }
}
