<?php

namespace PHPImpactor;

require_once "CURL.php";

use SimpleXMLElement;
use Exception;

final class User
{
    private $firstName = "";
    private $lastName = "";

    private $token;

    public function __construct(string $email, string $password)
    {
        $this->performAuth($email, $password);
    }

    /**
     * Gets full name of the user.
     *
     * @return string Returnes full name string.
     */
    public function getName(): string
    {
        return $this->firstName . " " . $this->lastName;
    }

    /**
     * Gets authentication token for the current user.
     *
     * @return string Returnes token string.
     */
    public function getToken(): string
    {
        return $this->token;
    }

    public function __toString()
    {
        $name = $this->getName();
        $token = $this->token;
        return "<User: name='$name';>";
    }

    /**
     * Performs authentication on apple.com for the current user.
     *
     * @param  string $email AppleID that will be used as login.
     * @param  string $password Password for user.
     *
     * @return void
     */
    private function performAuth(string $email, string $password)
    {
        $url = "https://idmsa.apple.com/IDMSWebAuth/clientDAW.cgi";
        $body = http_build_query([
            "appIdKey" => "ba2ec180e6ca6e6c6a542255453b24d6e6e5b2be0cc48bc1b0d8ad64cfe0228f",
            "appleId" => $email,
            "format" => "json",
            "password" => $password,
            "protocolVersion" => "A1234",
            "userLocale" => "en_US"
        ]);

        $headers = [
            "Content-Type: application/x-www-form-urlencoded"
        ];

        CURL::shared()->post($url, $body, $headers, function (string $response) {
            $json = json_decode($response, true);
            if ($json["resultCode"] != 0) {
                throw new Exception($json["resultString"]);
            }
            $this->token = $json["myacinfo"];
            $this->firstName = $json["firstName"];
            $this->lastName = $json["lastName"];
        });
    }
}