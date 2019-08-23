<?php

namespace PHPImpactor;

use ErrorException;

require_once "CURL.php";

class ProvisioningProfile
{
    /**
     * Type constant for distribution type
     * @var string
     */
    const TYPE_DISTRIBUTION = "store";

    /**
     * Type constant for development type
     * @var string
     */
    const TYPE_DEVELOPMENT = "limited";

    /**
     * Type constant for adhoc type
     * @var string
     */
    const TYPE_ADHOC = "adhoc";


    private $_name;
    private $_identifier;
    private $_status;
    private $_expire;
    private $_type;
    private $_appID;
    private $_rawProfile;
    private $_filename;

    public function __construct(array $response)
    {
        $this->_name = $response["name"];
        $this->_identifier = $response["provisioningProfileId"];
        $this->_status = $response["status"];
        $this->_type = $response["type"];
        $this->_appID = $response["appIdId"];
        $this->_filename = $response["filename"];
        $this->_rawProfile = isset($response["encodedProfile"]) ? $response["encodedProfile"] : "";
        $this->_expire = strtotime($response["dateExpire"]);
    }

    /**
     * Name of the profile on apple's database.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->_name;
    }

    /**
     * Unique identifier of the profile.
     *
     * @return string
     */
    public function identifier(): string
    {
        return $this->_identifier;
    }

    /**
     * Current status of the profile.
     *
     * @return string
     */
    public function status(): string
    {
        return $this->_status;
    }

    /**
     * UNIX timestamp of expiration date.
     *
     * @return int
     */
    public function expire(): int
    {
        return $this->_expire;
    }

    /**
     * Type of the profile.
     *
     * @return string
     */
    public function type(): string
    {
        return $this->_type;
    }

    /**
     * Application ID which profile belongs.
     *
     * @return string
     */
    public function appID(): string
    {
        return $this->_appID;
    }

    /**
     * Raw representation of the profile.
     *
     * @return string
     */
    public function rawProfile(): string
    {
        return $this->_rawProfile;
    }

    /**
     * File name of the profile.
     *
     * @return string
     */
    public function filename(): string
    {
        return $this->_filename;
    }

    /**
     * Saves profile to the specified file.
     *
     * @param string $path Path to which profile will be saved.
     *
     * @return void
     */
    public function save(string $path)
    {
        if (\is_writable($path)) {
            throw new ErrorException("Path is not writable! ($path");
        }

        $file = fopen($path, "w+");
        \fwrite($file, $this->_rawProfile);
        fclose($file);
    }

    /**
     * Retrieves current provisioning profiles in account.
     *
     * @param string $userToken User's auth token.
     * @param string $teamID User's unique team identifier.
     *
     * @return array Returnes array with profiles instances.
     */
    public static function profilesForUser(string $userToken, string $teamID): array
    {
        $body = [
            "teamId" => $teamID
        ];

        $profiles = [];
        CURL::shared()->privatePostAPI(
            "ios/listProvisioningProfiles.action",
            $userToken,
            $body,
            function (array $response) use (&$profiles) {
                foreach ($response["provisioningProfiles"] as $profile_array) {
                    $profiles[] = new self($profile_array);
                }
                unset($profiles);
            }
        );

        return $profiles;
    }

    /**
     * Retrieves full content of provisioning profile.
     *
     * @param string $userToken User's auth token.
     * @param string $teamID User's unique team identifier.
     * @param string $profileID Profile unique identifier.
     *
     * @return mixed Returnes full provisioning profile if present, in other cases - null.
     */
    public static function profileWithIdentifier(string $userToken, string $teamID, string $profileID)
    {
        $body = [
            "teamId" => $teamID,
            "provisioningProfileId" => $profileID
        ];

        $profile = null;
        CURL::shared()->privatePostAPI(
            "ios/downloadProvisioningProfile.action",
            $userToken,
            $body,
            function (array $response) use (&$profile) {
                $profile = new self($response["provisioningProfile"]);
            }
        );

        return $profile;
    }


    /**
     * Creates provisioning profile for specified certificate and appID.
     *
     * @param string $team Team identifier which profile will be added in.
     * @param string $type Type of the profile. Must be the type constant of ProvisioningProfile class.
     * @param array  $devices Array with devices identifiers that should be included in profile.
     * @param string $appID ApplicationID identifier which profile will be added in.
     * @param string $certificate Certificate identifier which which profile will be added in.
     * @param string $name Name of the profile.
     *
     * @return mixed Returnes instance of provisioning profile if is present. In other cases - null.
     */
    public static function create(string $userToken, string $type, array $devices_identifiers, string $teamID, string $appID, string $certificateID, string $name)
    {
        $body = [
            "teamId" => $teamID,
            "appIdId" => $appID,
            "distributionType" => $type,
            "provisioningProfileName" => $name,
            "certificateIds" => $certificateID,
            "deviceIds" => $devices_identifiers
        ];

        $profile = false;
        CURL::shared()->privatePostAPI(
            "ios/createProvisioningProfile.action",
            $userToken,
            $body,
            function (array $response) use (&$profile) {
                $profile = new self($response["provisioningProfile"]);
            }
        );

        return $profile;
    }
}