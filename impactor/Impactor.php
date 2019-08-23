<?php

namespace PHPImpactor;

require_once "CURL.php";
require_once "User.php";
require_once "Team.php";
require_once "Device.php";
require_once "ProvisioningProfile.php";
require_once "ApplicationID.php";
require_once "Certificate.php";


final class Impactor
{
    private $_user;


    /**
     * Creates instance and authiorizes user.
     *
     * @param string $email User email which will be used as AppleID.
     * @param string $password User password.
     *
     * @return void
     */
    public function __construct(string $email, string $password)
    {
        CURL::$defaultHeaders = [
            "Connection: keep-alive",
            "User-Agent: Xcode",
            "X-Xcode-Version: 7.0 (7A120f)",
            "Accept-Language: en_us"
        ];

        $this->_user = new User($email, $password);
    }

    /**
     * Retrieves user instance which was authorized.
     *
     * @return object
     */
    public function user(): object
    {
        return $this->_user;
    }

    /**
     * Retrieves teams of active user.
     *
     * @return array Returns array with team instances.
     * Note, this value will not be cached. So, you have to save it for later use.
     */
    public function teams(): array
    {
        return Team::teamsForUser($this->_user->getToken());
    }

    /**
     * Retrieves user devices for specifid team.
     *
     * @param object $team Team instance which will be used to get devices.
     *
     * @return array Returns array with device instances
     * Note, this value will not be cached. So, you have to save it for later use.
     */
    public function devices(object $team): array
    {
        return Device::devicesForTeam($this->_user->getToken(), $team->identifier());
    }

    /**
     * Finds device if it exists in account.
     *
     * @param array $devices Array with device instances.
     * @param string $udid UDID which will be used to check.
     *
     * @return mixed Returns device instance if it exists, in other cases - false.
     */
    public function findDevice(array $devices, string $udid)
    {
        foreach ($devices as $device) {
            if ($device->udid() === $udid) {
                return $device;
            }
        }

        return null;
    }

    /**
     * Adds device to account.
     *
     * @param object $team Team which device will be added in.
     * @param string $udid Unique ID iof the device.
     * @param string $name Name of the device in account.
     *
     * @return object Returns device instance.
     */
    public function addDevice(object $team, string $udid, string $name): object
    {
        return Device::addToAccount($this->_user->getToken(), $team->identifier(), $udid, $name);
    }

    /**
     * Retrieves current provisioning profiles in account.
     *
     * @param  object $team Team which proifiles should be checked in.
     *
     * @return array Returnes array with profiles instances.
     * Note, this value will not be cached. So, you have to save it for later use.
     */
    public function provisioningProfiles(object $team): array
    {
        return ProvisioningProfile::profilesForUser($this->_user->getToken(), $team->identifier());
    }

    /**
     * Retrieves full content of provisioning profile.
     *
     * @param  object $team Team which proifile should be checked in.
     * @param  object $profile Instance of provisioning profile.
     *
     * @return object Returnes full provisioning profile.
     * Note, this value will not be cached. So, you have to save it for later use.
     */
    public function downloadProfile(object $team, object $profile): object
    {
        return ProvisioningProfile::profileWithIdentifier(
            $this->_user->getToken(),
            $team->identifier(),
            $profile->identifier()
        );
    }

    /**
     * Creates provisioning profile for specified certificate and appID.
     *
     * @param object $team Instance of team object which profile will be added in.
     * @param string $type Type of the profile. Must be the type constant of ProvisioningProfile class.
     * @param array  $devices Array with devices instances that should be included in profile.
     * @param object $appID Instance of applicationID which profile will be added in.
     * @param object $certificate Instance of certificate which which profile will be added in.
     * @param string $name Name of the profile.
     *
     * @return mixed Returnes instance of provisioning profile if is present. In other cases - null.
     */
    public function createProfile(object $team, string $type, array $devices, object $appID, object $certificate, string $name)
    {
        $devices_identifiers = [];
        if (is_array($devices)) {
            foreach ($devices as $device) {
                $devices_identifiers[] = $device->identifier();
            }
        }
        return ProvisioningProfile::create(
            $this->_user->getToken(),
            $type,
            $devices_identifiers,
            $team->identifier(),
            $appID->identifier(),
            $certificate->identifier(),
            $name
        );
    }

    /**
     * Finds provisioning profile in array.
     *
     * @param  mixed $profiles Array to find in.
     * @param  mixed $name Name of the provisioning profile.
     *
     * @return void Returnes instance of provisioning profile if is present. In other cases - null.
     */
    public function findProfile(array $profiles, string $name)
    {
        foreach ($profiles as $profile) {
            if ($profile->name() === $name) {
                return $profile;
            }
        }

        return null;
    }

    /**
     * Retrieves current application identifiers.
     *
     * @param  mixed $team Instance of team object which indentifiers will be searched in.
     *
     * @return array Returnes array with applicationIDs instances.
     * Note, this value will not be cached. So, you have to save it for later use.
     */
    public function applicationIDS(object $team): array
    {
        return ApplicationID::appidsForTeam($this->_user->getToken(), $team->identifier());
    }

    /**
     * Retrieves current certificates.
     *
     * @param  mixed $team Instance of team object which certifcates will be searched in.
     *
     * @return array Returnes array with certificates instances.
     * Note, this value will not be cached. So, you have to save it for later use.
     */
    public function certificates(object $team): array
    {
        return Certificate::certificatesForUser($this->_user->getToken(), $team->identifier());
    }
}