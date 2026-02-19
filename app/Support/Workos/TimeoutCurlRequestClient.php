<?php

namespace App\Support\Workos;

use WorkOS\Client;
use WorkOS\Exception\GenericException;
use WorkOS\RequestClient\RequestClientInterface;

class TimeoutCurlRequestClient implements RequestClientInterface
{
    private int $connectTimeoutSeconds;
    private int $timeoutSeconds;

    public function __construct(int $connectTimeoutSeconds = 10, int $timeoutSeconds = 20)
    {
        $this->connectTimeoutSeconds = max(1, $connectTimeoutSeconds);
        $this->timeoutSeconds = max($this->connectTimeoutSeconds, $timeoutSeconds);
    }

    public function request($method, $url, ?array $headers = null, ?array $params = null)
    {
        $headers = $headers ?? [];

        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeoutSeconds,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
        ];

        switch ($method) {
            case Client::METHOD_GET:
                if (!empty($params)) {
                    $url .= '?' . http_build_query($params);
                }
                break;

            case Client::METHOD_POST:
                $headers[] = 'Content-Type: application/json';
                $opts[CURLOPT_POST] = true;
                if (!empty($params)) {
                    $opts[CURLOPT_POSTFIELDS] = json_encode($params);
                }
                break;

            case Client::METHOD_DELETE:
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;

            case Client::METHOD_PUT:
                $headers[] = 'Content-Type: application/json';
                $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $opts[CURLOPT_POST] = true;
                if (!empty($params)) {
                    $opts[CURLOPT_POSTFIELDS] = json_encode($params);
                }
                break;
        }

        $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_URL] = $url;

        return $this->execute($opts);
    }

    private function execute(array $opts): array
    {
        $curl = curl_init();

        $responseHeaders = [];
        $opts[CURLOPT_HEADERFUNCTION] = function ($curl, $headerLine) use (&$responseHeaders) {
            if (strpos($headerLine, ':') === false) {
                return strlen($headerLine);
            }

            [$key, $value] = explode(':', trim($headerLine), 2);
            $responseHeaders[trim($key)] = trim($value);

            return strlen($headerLine);
        };

        curl_setopt_array($curl, $opts);
        $result = curl_exec($curl);

        if ($result === false) {
            $errno = curl_errno($curl);
            $message = curl_error($curl);
            curl_close($curl);

            throw new GenericException($message, ['curlErrno' => $errno]);
        }

        $statusCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        return [$result, $responseHeaders, $statusCode];
    }
}

