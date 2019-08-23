<?php

namespace PHPImpactor;

require_once "CURL.php";

final class Team
{
    private $_name;
    private $_identifier;
    private $_status;
    private $_created;

    public function __construct(array $response)
    {
        $this->_name = $response["name"];
        $this->_identifier = $response["teamId"];
        $this->_status = $response["status"];
        $this->_created = $response["dateCreated"];
    }

    /**
     * Gets name of team.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->_name;
    }

    /**
     * Gets team identifier. This value is unique for all Apple users.
     *
     * @return string
     */
    public function identifier(): string
    {
        return $this->_identifier;
    }

    /**
     * Gets team status. In most cases it will be 'active'.
     *
     * @return string
     */
    public function status(): string
    {
        return $this->_status;
    }

    /**
     * Gets team creation UNIX timestamp.
     *
     * @return int
     */
    public function createdTimestamp(): int
    {
        return $this->_created;
    }

    public function __toString()
    {
        $name = $this->_name;
        $identifier = $this->_identifier;
        $status = $this->_status;
        $created = \date("r", $this->_created);
        return "<Team: name=$name; identifier=$identifier; status=$status; created=$created;>";
    }


    public static function teamsForUser(string $userToken): array
    {
        $teams = [];
        CURL::shared()->privatePostAPI("listTeams.action", $userToken, null, function (array $response) use (&$teams) {
            foreach ($response["teams"] as $team_array) {
                $teams[] = new Team($team_array);
            }
            unset($teams);
        });

        return $teams;
    }
}
