<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Throwable;

class ErrorHandlingService
{
    /**
     * Handle different types of exceptions and return appropriate responses
     *
     * @param Throwable $exception
     * @return JsonResponse
     */
    public function handleException(Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return $this->handleValidationException($exception);
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException($exception);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->handleAuthenticationException($exception);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->handleAuthorizationException($exception);
        }

        if ($exception instanceof QueryException) {
            return $this->handleQueryException($exception);
        }

        if ($exception instanceof HttpException) {
            return $this->handleHttpException($exception);
        }

        return $this->handleGenericException($exception);
    }

    /**
     * Handle validation exceptions
     *
     * @param ValidationException $exception
     * @return JsonResponse
     */
    protected function handleValidationException(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $exception->errors(),
        ], 422);
    }

    /**
     * Handle model not found exceptions
     *
     * @param ModelNotFoundException $exception
     * @return JsonResponse
     */
    protected function handleModelNotFoundException(ModelNotFoundException $exception): JsonResponse
    {
        $model = strtolower(class_basename($exception->getModel()));
        return response()->json([
            'message' => "Unable to find {$model} with the given id.",
        ], 404);
    }

    /**
     * Handle authentication exceptions
     *
     * @param AuthenticationException $exception
     * @return JsonResponse
     */
    protected function handleAuthenticationException(AuthenticationException $exception): JsonResponse
    {
        return response()->json([
            'message' => 'Unauthenticated.',
        ], 401);
    }

    /**
     * Handle authorization exceptions
     *
     * @param AuthorizationException $exception
     * @return JsonResponse
     */
    protected function handleAuthorizationException(AuthorizationException $exception): JsonResponse
    {
        return response()->json([
            'message' => 'This action is unauthorized.',
        ], 403);
    }

    /**
     * Handle query exceptions
     *
     * @param QueryException $exception
     * @return JsonResponse
     */
    protected function handleQueryException(QueryException $exception): JsonResponse
    {
        return response()->json([
            'message' => 'Database error occurred.',
            'error' => config('app.debug') ? $exception->getMessage() : 'Please try again later.',
        ], 500);
    }

    /**
     * Handle HTTP exceptions
     *
     * @param HttpException $exception
     * @return JsonResponse
     */
    protected function handleHttpException(HttpException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage() ?: 'An error occurred.',
        ], $exception->getStatusCode());
    }

    /**
     * Handle generic exceptions
     *
     * @param Throwable $exception
     * @return JsonResponse
     */
    protected function handleGenericException(Throwable $exception): JsonResponse
    {
        return response()->json([
            'message' => 'An unexpected error occurred.',
            'error' => config('app.debug') ? $exception->getMessage() : 'Please try again later.',
        ], 500);
    }
} 