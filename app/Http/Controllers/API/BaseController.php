<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller ;


class BaseController extends Controller
{
    public function sendResponse($result, $message, $status = 200) // أضيفي = 200 هنا
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $status);
    }

    public function sendError(string $message, array $errorMessages = [], int $status)
    {
        $response = [
            'message' => $message,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $status);
    }

    public function sendPaginatedResponse($paginator, string $message = '', int $status )
    {
        return response()->json([
            'message' => $message,
            'data'    => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ], $status);
    }
}
