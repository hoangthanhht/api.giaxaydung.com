<?php

namespace App\Exceptions;
use App\Exceptions\RemoteException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
    // public function report(Exception $e)
    // {
    //     // if ($exception instanceof CustomException) {
    //     //     // Logging exception
    //     // }

    //     // parent::report($exception);

    //     if ($e instanceof ModelNotFoundException) {
    //         $e = new NotFoundHttpException($e->getMessage(), $e);
    //     } elseif ($e instanceof AuthorizationException) {
    //         $e = new HttpException(403, $e->getMessage());
    //     } elseif ($e instanceof TokenMismatchException) {
    //         $e = new HttpException(419, $e->getMessage());
    //     }
    // }


    // public function render($request, Throwable $exception)
    // {
    //     if( $request->is('api/*')){
    //         if ($exception instanceof ModelNotFoundException) {
    //                      $model = strtolower(class_basename($exception->getModel()));
                       
    //       return response()->json([
    //                  'error' => 'Model not found'
    //              ], 404);
    //                  }
    //        if ($exception instanceof NotFoundHttpException) {
    //       return response()->json([
    //                  'error' => 'Resource not found'
    //              ], 404);
                         
    //                  }
    //      }
    // }

    protected function context()
    {
        return array_merge(parent::context(), [
            'foo' => 'bar',
        ]);
    }

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function handleException($request, Exception $exception)
    {
        
        if($exception instanceof RemoteException) {
           return response('The specified URL cannot be  found.', 404);
        }
    }
    public function register()
    {
        // $this->reportable(function (Throwable $e) {
        //     //
        // });

        
        // $this->renderable(function(Throwable $exception) {
           
        //     if ($exception){

        //         return response()->json(["er"=>$exception],500);
        //     }
        // });


        // $this->renderable(function(Exception $e, $request) {
        //     return $this->handleException($request, $e);
        // });
        // $this->renderable(function (Throwable $e) {
        //     //
        //     return response(['error' => $e->getMessage()], $e->getCode() ?: 400);
        // });
    }
}
