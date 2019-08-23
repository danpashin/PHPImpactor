<?php

namespace PHPImpactor;

require_once "CURL.php";

final class ApplicationID
{

    private $_identifier;
    private $_name;
    private $_signID;
    private $_wildcard;

    public function __construct(array $response)
    {
        $this->_identifier = $response["appIdId"];
        $this->_name = $response["name"];
        $this->_signID = $response["identifier"];
        $this->_wildcard = $response["isWildCard"];
    }

    public function __toString()
    {
        $appIdId = $this->_identifier;
        $name = $this->_name;
        $signID = $this->_signID;
        $wildcard = $this->_wildcard;
        return "<ApplicationID: appIdId=$appIdId; name='$name'; signID='$signID'; wildcard=$wildcard>";
    }

    /**
     * Unique identifier of the application ID.
     *
     * @return string
     */
    public function identifier(): string
    {
        return $this->_identifier;
    }

    /**
     * Name of the application ID.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->_name;
    }

    /**
     * Unique identifier used to signing.
     *
     * @return string
     */
    public function signID(): string
    {
        return $this->_signID;
    }

    /**
     * Checks if application ID supports wildcard identifiers.
     *
     * @return bool
     */
    public function wildcard(): bool
    {
        return $this->_wildcard;
    }

    /**
     * Retrieves application identifiers for specified team.
     *
     * @param string $userToken User's auth token.
     * @param string $teamIdentifier User's unique team identifier.
     *
     * @return array Returnes array of appIDs instances.
     */
    public static function appidsForTeam(string $userToken, string $teamIdentifier): array
    {
        $body = [
            "teamId" => $teamIdentifier
        ];

        $appids = [];
        CURL::shared()->privatePostAPI(
            "ios/listAppIds.action",
            $userToken,
            $body,
            function (array $response) use (&$appids) {
                foreach ($response["appIds"] as $appid_array) {
                    $appids[] = new self($appid_array);
                }
                unset($appids);
            }
        );

        return $appids;
    }
}
