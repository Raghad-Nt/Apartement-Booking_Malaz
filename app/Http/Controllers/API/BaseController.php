<?php
  
namespace App\Http\Controllers\API;
  
use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
  
class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message): JsonResponse
    {
        $response = [
            'message' => __($message),
            'data'    => $result,
           
        ];  
          
        return new JsonResponse($response, 200);
    }
  
   /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = []): JsonResponse
    {
        $response = [
           
            'message' => __($error),
        ];
  
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
  
        return new JsonResponse($response, 404);
    }

      
     //Send paginated response
     
    public function sendPaginatedResponse($paginator, $message = ''): JsonResponse
    {
        $response = [

            
            'data' => $paginator->items(),
            'message' => __($message),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ];

        return new JsonResponse($response, 200);
    }
}