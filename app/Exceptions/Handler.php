<?php

namespace App\Exceptions;

use App\Services\V1\ResponseCode;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Iqbalatma\LaravelJwtAuthentication\Exceptions\MissingRequiredTokenException;
use Iqbalatma\LaravelUtils\APIResponse;
use Iqbalatma\LaravelUtils\Traits\APIResponseTrait;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use APIResponseTrait;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (ValidationException $e) {
            if (request()->expectsJson()) {
                return new APIResponse(
                    null,
                    $e->getMessage(),
                    ResponseCode::ERR_VALIDATION(),
                    errors: $e->errors(),
                    exception: $e
                );
            }
        });

        $this->renderable(function (NotFoundHttpException $e) {
            if (request()->expectsJson()) {
                return new APIResponse(
                    null,
                    message: $e->getMessage(),
                    responseCode: ResponseCode::ERR_NOT_FOUND(),
                    exception: $e
                );
            }
        });

        $this->renderable(function (HttpExceptionInterface $e) {
            if (request()->expectsJson()) {
                return new APIResponse(
                    null,
                    $e->getMessage(),
                    exception: $e
                );
            }
        });

        $this->renderable(function (UnauthorizedException $e) {
            if (request()->expectsJson()) {
                return new APIResponse(
                    null,
                    $e->getMessage(),
                    responseCode: ResponseCode::ERR_FORBIDDEN(),
                    exception: $e
                );
            }
        });


        $this->renderable(function (AuthenticationException $e) {
            if (request()->expectsJson()) {
                return new APIResponse(
                    null,
                    $e->getMessage(),
                    responseCode: ResponseCode::ERR_UNAUTHENTICATED(),
                    exception: $e
                );
            }
        });

        $this->renderable(function (MissingRequiredTokenException $e) {
            if (request()->expectsJson()) {
                return new APIResponse(
                    null,
                    $e->getMessage(),
                    responseCode: ResponseCode::ERR_UNAUTHENTICATED(),
                    exception: $e
                );
            }
        });


        $this->renderable(function (Throwable|Exception $e) {
            if (request()->expectsJson()) {
                return new APIResponse(
                    null,
                    config("app.env") === "production" ? "Something went wrong" : $e->getMessage(),
                    exception: $e
                );
            }
        });
    }
}
