<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;


class Handler extends ExceptionHandler
{
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
    }
    
    protected function handleApiException($request, Throwable $exception)
    {
        $status = 500;
        $response = [
            'status' => false,
            'message' => $exception->getMessage(),
        ];

        return new JsonResponse($response, $status);
    }
    
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Check if the exception is a MethodNotAllowedHttpException
        if ($exception instanceof MethodNotAllowedHttpException) {

            // Return a custom response for the 405 error
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage()
            ], 405);
        }

        // Handle NotFoundHttpException
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'status' => false,
                'message' =>  $exception->getMessage()
            ], 404);
        }

        if ($exception instanceof AuthenticationException || $exception instanceof UnauthorizedHttpException) {
            return response()->json([
                'status' => false,
                'message' =>  'Invalid token. Please provide a valid token.'
            ], 401);
        }


        if ($request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }
}
