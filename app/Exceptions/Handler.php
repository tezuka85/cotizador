<?php

namespace App\Exceptions;

use App\Events\LogHttpRequest;
//use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use KeycloakGuard\Exceptions\TokenException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     * @param Exception $exception
     * @return mixed|void
     * @throws Exception
     * @version 2.0
     */
    /*public function report(Exception $exception)
    {
        parent::report($exception);
    }*/

    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
//        die(print_r($exception->getCode()));
        Log::error($exception->getFile().' '.$exception->getLine().' '.$exception->getMessage());

        $response = [
            'success' => false,
            'message' => 'Error',
            'error' => $exception->getMessage()
        ];

            if ($exception instanceof UnauthorizedException) {

                $code = 403;
                event(new LogHttpRequest($request, $response,$code));
                return response()->json($response, $code);

            }elseif ($exception instanceof NotFoundHttpException) {
                $code = 404;
                $response = [
                    'success' => false,
                    'message' => 'Ruta no existe',
                    'error' => "Ruta no existe o mal formada"
                ];

                event(new LogHttpRequest($request, $response,$code));
                return response()->json($response, $code);

            }else if ($exception instanceof ValidationException) {
                $code = 422;

                $response = [
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'error' => $exception->validator->getMessageBag()
                ];

                event(new LogHttpRequest($request, $response,$code));
                return response()->json($response, $code);

            }else if ($exception instanceof TokenException) {
                $code = 401;

                $response = [
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'error' => $exception->getMessage()
                ];

                event(new LogHttpRequest($request, $response,$code));
                return response()->json($response, $code);

            }
            else if($exception instanceof AuthenticationException){
                $code = 401;

                $response = [
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'error' => $exception->getMessage()
                ];
                event(new LogHttpRequest($request, $response,$code));
                return response()->json($response, $code);

            }else if ($exception instanceof Throwable) {
                $code = 500;
                $message = utf8_encode($exception->getMessage());
                    //die(print_r($exception->getMessage()));
                $response = [
                    'success' => false,
                    'message' => 'Error',
                    'error' =>$message
                ];

                Log::error($exception->getTraceAsString());
                return response()->json($response, $code);
            }

            return parent::render($request, $exception);
    }


}
