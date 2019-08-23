<?php

namespace PHPImpactor;

require_once "CURL.php";

final class Certificate
{

    private $_identifier;
    private $_name;
    private $_rawCertificate;

    public function __construct(array $response)
    {
        $this->_name = $response["name"];
        $this->_identifier = $response["certificateId"];
        $this->_rawCertificate = $response["certContent"];
    }

    public function __toString()
    {
        $udid = $this->_udid;
        $model = $this->_model;
        return "<Device: udid=$udid model='$model'>";
    }

    /**
     * Unique identifier of the certificate.
     *
     * @return string
     */
    public function identifier(): string
    {
        return $this->_identifier;
    }

    /**
     * Name of the certificate.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->_name;
    }

    /**
     * Raw contents of the certificate.
     *
     * @return string
     */
    public function rawCertificate(): string
    {
        return $this->_rawCertificate;
    }

    /**
     * Retrieves current certificates.
     *
     * @param  mixed $userToken User's auth token.
     * @param  mixed $teamIdentifier User's unique team identifier.
     *
     * @return array Returnes array of certificates instances.
     */
    public static function certificatesForUser(string $userToken, string $teamIdentifier): array
    {
        $body = [
            "teamId" => $teamIdentifier
        ];

        $certificates = [];
        CURL::shared()->privatePostAPI(
            "ios/listAllDevelopmentCerts.action",
            $userToken,
            $body,
            function (array $response) use (&$certificates) {
                foreach ($response["certificates"] as $certificate_array) {
                    $certificates[] = new self($certificate_array);
                }
                unset($certificates);
            }
        );

        CURL::shared()->privatePostAPI(
            "ios/downloadDistributionCerts.action",
            $userToken,
            $body,
            function (array $response) use (&$certificates) {
                foreach ($response["certificates"] as $certificate_array) {
                    $certificates[] = new self($certificate_array);
                }
                unset($certificates);
            }
        );

        return $certificates;
    }
}