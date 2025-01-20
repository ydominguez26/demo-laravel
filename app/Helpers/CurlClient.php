<?php

namespace app\Helpers;
use Illuminate\Support\Facades\Log;

class CurlClient
{
    /**
     * Curl post request
     *
     * @param $url
     * @param $header
     * @param $params
     * @return bool|string
     */
    public static function post(string $url, string $header, array $params)
    {
        $postData = self::structureParams($params);

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_POST, $postData);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * Curl get request
     *
     * @param $url
     * @param $header
     * @return bool|string
     */
    public static function get(string $url, string $header)
    {
        $ch = curl_init();

        Log::info("Client.httpGet: " . $url);
        Log::info("Client.httpGet: " . $header);

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HTTPHEADER,array($header));

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     *  Structure parameters
     *
     * @param array $params
     * @return string
     */
    private static function structureParams(array $params) : string
    {
        $postData = '';

        //create name value pairs seperated by &
        foreach($params as $k => $v)
        {
            $postData .= $k . '='.$v.'&';
        }

        return rtrim($postData, '&');
    }
}
