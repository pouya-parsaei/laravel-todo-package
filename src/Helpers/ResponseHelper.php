<?php

namespace PouyaParsaei\LaravelToDo\Helpers;

use Error;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;

trait ResponseHelper
{
    protected $statusCode;

    public function respondSuccess(string $message, array $data)
    {
        return $this->setStatusCode(200)->respond($message, true, $data);

    }

    public function respondCreated(string $message, array $data)
    {
        return $this->setStatusCode(201)->respond($message, true, $data);

    }

    public function respondNotFound(string $message)
    {
        return $this->setStatusCode(404)->respond($message);

    }

    public function respondNotAuthorized(string $message)
    {
        return $this->setStatusCode(403)->respond($message);

    }

    public function respondNotAuthenticated(string $message)
    {
        return $this->setStatusCode(401)->respond($message);

    }

    public function respondInternalError($message)
    {
        return $this->setStatusCode(500)->respond($message);
    }

    private function respond(string $message = '', bool $isSuccess = false, array $data = null)
    {
        $responseData = [
            'success' => $isSuccess,
            'message' => $message,
            'data' => $data
        ];
        return response()->json($responseData)->setStatusCode($this->getStatusCode());
    }

    private function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    private function getStatusCode()
    {
        return $this->statusCode;
    }


    protected function respondWithResource(JsonResource $resource, $message = null, $statusCode = 200, $headers = [])
    {

        return $this->apiResponse(
            [
                'success' => true,
                'result' => $resource,
                'message' => $message
            ], $statusCode, $headers
        );
    }

    private function apiResponse($data = [], $statusCode = 200, $headers = [])
    {
        $result = $this->parseGivenData($data, $statusCode, $headers);

        return response()->json(
            $result['content'], $result['statusCode'], $result['headers']
        );
    }

    private function parseGivenData($data = [], $statusCode = 200, $headers = [])
    {
        $responseStructure = [
            'success' => $data['success'],
            'message' => $data['message'] ?? null,
            'data' => $data['result'] ?? null,
        ];
        if (isset($data['errors'])) {
            $responseStructure['errors'] = $data['errors'];
        }
        if (isset($data['status'])) {
            $statusCode = $data['status'];
        }


        if (isset($data['exception']) && ($data['exception'] instanceof Error || $data['exception'] instanceof Exception)) {
            if (config('app.env') !== 'production') {
                $responseStructure['exception'] = [
                    'message' => $data['exception']->getMessage(),
                    'file' => $data['exception']->getFile(),
                    'line' => $data['exception']->getLine(),
                    'code' => $data['exception']->getCode(),
                    'trace' => $data['exception']->getTrace(),
                ];
            }

            if ($statusCode === 200) {
                $statusCode = 500;
            }
        }
        if ($data['success'] === false) {
            if (isset($data['error_code'])) {
                $responseStructure['error_code'] = $data['error_code'];
            } else {
                $responseStructure['error_code'] = 1;
            }
        }
        return ["content" => $responseStructure, "statusCode" => $statusCode, "headers" => $headers];
    }
}
