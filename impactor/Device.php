<?php

namespace PHPImpactor;

require_once "CURL.php";

final class Device
{

    private $_identifier;
    private $_name;
    private $_udid;
    private $_platform;
    private $_status;
    private $_class;
    private $_model;

    public function __construct(array $response)
    {
        $this->_identifier = $response["deviceId"];
        $this->_name = $response["name"];
        $this->_udid = $response["deviceNumber"];
        $this->_platform = $response["devicePlatform"];
        $this->_status = $response["status"];
        $this->_class = $response["deviceClass"];
        $this->_model = isset($response["model"]) ? $response["model"] : "";
    }

    public function __toString()
    {
        $udid = $this->_udid;
        $model = $this->_model;
        return "<Device: udid=$udid model='$model'>";
    }

    /**
     * Gets device identifier in account.
     *
     * @return string
     */
    public function identifier(): string
    {
        return $this->_identifier;
    }

    /**
     * Gets device name in account.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->_name;
    }

    /**
     * Gets Unique Device ID (UDID) of the device.
     *
     * @return string
     */
    public function udid(): string
    {
        return $this->_udid;
    }

    /**
     * Gets platform of the device.
     *
     * @return string
     */
    public function platform(): string
    {
        return $this->_platform;
    }

    /**
     * Gets device account status. In most cases it would be 'c'.
     *
     * @return string
     */
    public function status(): string
    {
        return $this->_status;
    }

    /**
     * Gets device class (e.g. iPad, iPhone)
     *
     * @return string
     */
    public function class(): string
    {
        return $this->_class;
    }

    /**
     * Gets device model (e.g. iPad Pro 11-inch Wi-Fi + Cellular )
     *
     * @return string
     */
    public function model(): string
    {
        return $this->_model;
    }

    /**
     * Gets devices for specified team.
     *
     * @param string $teamIdentifier Unique team identifier.
     * @param string $userToken User token which will be used to get devices.
     *
     * @return array Returnes array of device objects.
     */
    public static function devicesForTeam(string $userToken, string $teamIdentifier): array
    {
        $body = [
            "teamId" => $teamIdentifier
        ];

        $devices = [];
        CURL::shared()->privatePostAPI(
            "ios/listDevices.action",
            $userToken,
            $body,
            function (array $response) use (&$devices) {
                foreach ($response["devices"] as $device_array) {
                    $devices[] = new self($device_array);
                }
                unset($devices);
            }
        );

        return $devices;
    }

    /**
     * Adds device to account.
     *
     * @param string $userToken User's auth token.
     * @param string $teamID User's unique team identifier.
     * @param string $deviceUDID Unique device identfier.
     * @param string $deviceName Device name in account.
     *
     * @return mixed Returns instance of the device if present, in other cases - null.
     */
    public static function addToAccount(string $userToken, string $teamID, string $deviceUDID, string $deviceName)
    {
        $body = [
            "teamId" => $teamID,
            "deviceNumber" => $deviceUDID,
            "name" => $deviceName
        ];

        $device = null;
        CURL::shared()->privatePostAPI(
            "ios/addDevice.action",
            $userToken,
            $body,
            function (array $response) use (&$device) {
                $device = new self($response["device"]);
            }
        );

        return $device;
    }
}