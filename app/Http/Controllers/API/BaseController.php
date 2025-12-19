<?php
  
namespace App\Http\Controllers\API;
  
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
  
class BaseController extends Controller
{
    
    public function sendResponse($result, $message): JsonResponse
    {
        $response = [
            'message' => __($message),
            'data'    => $result
            
        ];
  
        return response()->json($response, 200);
    }
  
   
    public function sendError($error, $errorMessages = []): JsonResponse
    {
        $response = [
            'message' => __($error)
        ];
  
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
  
        return response()->json($response, 404);
    }

      
     
    public function sendPaginatedResponse($paginator, $message = ''): JsonResponse
    {
        $response = [
            'message' => __($message),
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ];

        return response()->json($response, 200);
    }
}