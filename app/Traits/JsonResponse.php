<?php
namespace App\Traits;

use Modules\V1api\Services;

trait JsonResponse
{
    /**
     * Success Response
     *
     * @param $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($data, $success = true, $code = 200, $headers = [], $options = 0) {
        return response()->json([
            'success' => $success,
            'data' => $data,
        ], $code, $headers, $options);
    }

    /**
     * Error Response
     *
     * @param string $message
     * @param array $errors
     * @param int $code
     * @param int $httpCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function error($data, $code = 400, $httpCode = 400) {
        return response()->json([
            'success' => false,
            'error' => $data
        ], $httpCode);
    }

    public function successV1api($data, $success = true) {
        $response = [];
        $response['success'] = $success;
        foreach($data as $key=>$value) {
            $response[$key] = $value;
        }
        if (\App::environment() != "production" && \App::environment() != "staging") {
        	$response['debug'] = $this->debug();
        }
        return response()->json($response);
    }

    protected function debug()
    {
        $client = Services\BApi\Client::getInstance();
        $bDebug = $client->debug();
        
        $client = Services\KApi\Client::getInstance();
        $kDebug = $client->debug();
        
        foreach ($kDebug as $key => $value) {
            $bDebug[] = [$value];
        }
 
        return $bDebug;
    }
}
