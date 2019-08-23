<?php

namespace PHPImpactor;

require_once "UUID.php";

use Exception;
use \CFPropertyList\CFPropertyList;
use \CFPropertyList\CFTypeDetector;

final class CURL
{
    public static $defaultHeaders;

    private $curl;

    /**
     * Initializes class singletone.
     *
     * @return object Returns singletone instance.
     */
    public static function shared(): object
    {
        static $instance = null;
        if (!$instance) {
            $instance = new CURL();
        }

        return $instance;
    }

    public function __construct()
    {
        $this->curl = curl_init();
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }

    /**
     * Sends post request to specified host.
     *
     * @param string $url Host address with URI components.
     * @param string $body Body of the request.
     * @param array $headers Additional headers for this request. Default is null.
     * @param callable $handle Private function that will be called on completion.
     * Must contain one arguments: response body (string).
     *
     * @return void
     */
    public function post(string $url, string $body, array $headers = null, callable $handle)
    {
        $request_headers = isset($headers) ? array_merge(self::$defaultHeaders, $headers) : self::$defaultHeaders;

        curl_setopt_array($this->curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $request_headers,
        ]);

        $response = curl_exec($this->curl);

        $error = curl_error($this->curl);
        if (strlen($error) > 0) {
            throw new Exception($error);
        }

        if ($response === false) {
            throw new Exception("Cannot execute request");
        }

        $handle($response);
    }

    /**
     * Sends request to private Apple API.
     *
     * @param string $path Absoule URI path.
     * @param string $uToken User token which will be used for API.
     * @param array $body Body of the request.
     * @param callable $handle Private function that will be called on completion.
     * Must contain one arguments: response body (string).
     *
     * @return void
     */
    public function privatePostAPI(string $path, string $uToken, array $body = null, callable $handle)
    {
        $url = "https://developerservices2.apple.com/services/QH65B2/$path?clientId=XABBG36SBA";

        $headers = [
            "Cookie: myacinfo=$uToken",
            "Content-Type: text/x-xml-plist",
            "Accept: text/x-xml-plist"
        ];

        $body = isset($body) ? $body : [];
        $structure = array_merge($body, [
            "clientId" => "XABBG36SBA",
            "protocolVersion" => "QH65B2",
            "myacinfo" => $uToken,
            "requestId" => UUID::generate()
        ]);

        $td = new CFTypeDetector();
        $guessedStructure = $td->toCFType($structure);

        $plist = new CFPropertyList();
        $plist->add($guessedStructure);

        $this->post($url, $plist->toXML(), $headers, function (string $response) use ($handle) {
            $uncompressed_response = @\gzdecode($response) ?: $response;

            $plist = new CFPropertyList();
            $plist->parse($uncompressed_response, CFPropertyList::FORMAT_XML);

            $array = $plist->toArray();
            if ($array["resultCode"] != 0) {
                $message = (isset($array["validationMessages"]) && count($array["validationMessages"])) > 0 ?
                    $array["validationMessages"][0] : $array["resultString"];

                throw new Exception($message);
            }

            $handle($array);
        });
    }
}