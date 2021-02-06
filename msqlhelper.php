<?php
/*
 * author : ph09nix
 * github : github.com/ph09nix
 * telegram : @ph09nix
 * gmail : ph09nixom@gmail.com
 */
{
	
    class msqlhelper
    {
        private string $db_name = "";
        private string $db_username = "";
        private string $db_password = "";
        private string $db_host = "localhost";
        private bool $debug_mod = false;
        private mysqli $mysqli_connection;

        public function __construct($host, $username, $password, $name, $debug_mod = false)
        {
            $this->db_name = $name;
            $this->db_username = $username;
            $this->db_password = $password;
            $this->db_host = $host;
            $this->debug_mod = $debug_mod;
            if ($debug_mod) {
                echo <<<body
MSQLHelper initialized
<br>
body;

            }
        }

        public function __destruct()
        {
            $this->mysqli_connection->close();
            if ($this->debug_mod) {
                echo <<<body
MSQLHelper flushed
<br>
body;

            }
        }

        public function connect(&$error): bool
        {
            $res = false;
            $this->mysqli_connection = new mysqli($this->db_host, $this->db_username, $this->db_password,
                $this->db_name);
            if ($this->mysqli_connection->connect_errno) {
                $error = $this->mysqli_connection->connect_error;
            } else {
                $error = "";
                $res = true;
            }
            return $res;
        }

        public function update(string $tb_name, array $params, array $conditions)
        {
            if (isset($conditions[0])) {
                $check = $this->select($tb_name, []);
                if (count($check) > 0) {
                    $columnsname = [];
                    $query = $this->mysqli_connection->query(<<<body
SELECT *
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME ="{$tb_name}"
AND TABLE_SCHEMA="{$this->db_name}"
body
                    );
                    while (($row = $query->fetch_assoc()) != null) {
                        $columnsname[] = $row["COLUMN_NAME"];
                    }
                    $newconditions = [];
                    for ($i = 0; $i < count($columnsname); ++$i) {
                        if (isset($conditions[$i])) {
                            $newconditions[$columnsname[$i]] = $conditions[$i];
                        }
                    }
                    $conditions = $newconditions;
                }
            }
            if (isset($params[0])) {
                $check = $this->select($tb_name, []);
                if (count($check) > 0) {
                    $columnsname = [];
                    $query = $this->mysqli_connection->query(<<<body
SELECT *
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME ="{$tb_name}"
AND TABLE_SCHEMA="{$this->db_name}"
body
                    );
                    while (($row = $query->fetch_assoc()) != null) {
                        $columnsname[] = $row["COLUMN_NAME"];
                    }
                    $newparams = [];
                    for ($i = 0; $i < count($columnsname); ++$i) {
                        if (isset($params[$i])) {
                            $newparams[$columnsname[$i]] = $params[$i];
                        }
                    }
                    $params = $newparams;
                }
            }
            $query_str = $this->generatequery("update", $tb_name, $params, $conditions);
            $query = $this->mysqli_connection->query($query_str);
            if ($this->debug_mod) {
                echo <<<body
update query generated-> {$query_str}
<br>
body;
            }
        }

        public function insert(string $tb_name, array $params)
        {
            if (isset($params[0])) {
                // convert sequential array to associative
                $check = $this->select($tb_name, []);
                if (count($check) > 0) {
                    $columnsname = [];
                    $query = $this->mysqli_connection->query(<<<body
SELECT *
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME ="{$tb_name}"
AND TABLE_SCHEMA="{$this->db_name}"
body
                    );
                    while (($row = $query->fetch_assoc()) != null) {
                        $columnsname[] = $row["COLUMN_NAME"];
                    }
                    $newparams = [];
                    for ($i = 0; $i < count($columnsname); ++$i) {
                        if (isset($params[$i])) {
                            $newparams[$columnsname[$i]] = $params[$i];
                        } else {
                            $newparams[$columnsname[$i]] = null;
                        }
                    }
                    $params = $newparams;
                }
            }
            $query_str = $this->generatequery("insert", $tb_name, $params, []);
            $this->mysqli_connection->query($query_str);
            if ($this->debug_mod) {
                echo <<<body
insert query generated-> {$query_str}
<br>
body;
            }
            $check = count($this->select($tb_name, $params)) > 0;
            return $check;
        }

        public function delete(string $tb_name, array $conditions)
        {
            if (isset($conditions[0])) {
                // convert sequential array to associative
                $check = $this->select($tb_name, []);
                if (count($check) > 0) {
                    $columnsname = [];
                    $query = $this->mysqli_connection->query(<<<body
SELECT *
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME ="{$tb_name}"
AND TABLE_SCHEMA="{$this->db_name}"
body
                    );
                    while (($row = $query->fetch_assoc()) != null) {
                        $columnsname[] = $row["COLUMN_NAME"];
                    }
                    $newconditions = [];
                    for ($i = 0; $i < count($columnsname); ++$i) {
                        if (isset($conditions[$i])) {
                            $newconditions[$columnsname[$i]] = $conditions[$i];
                        } else {
                            $newconditions[$columnsname[$i]] = null;
                        }
                    }
                    $conditions = $newconditions;
                }
            }
            $query_str = $this->generatequery("delete", $tb_name, [], $conditions);
            $this->mysqli_connection->query($query_str);
            if ($this->debug_mod) {
                echo <<<body
delete query generated-> {$query_str}
<br>
body;
            }
            $check = count($this->select($tb_name, $conditions)) > 0;
            return !$check;
        }

        public function select(string $tb_name, array $conditions): array
        {
            $res = [];
            $query_str = $this->generatequery("select", $tb_name, [], $conditions);
            $query = $this->mysqli_connection->query($query_str);
            if ($this->debug_mod) {
                echo <<<body
select query generated-> {$query_str}
query responses -> {$query->num_rows}
<br>
body;
            }
            if ($query->num_rows > 0) {
                while (($row = $query->fetch_assoc()) != null) {
                    $res[] = $row;
                }
            }
            return $res;
        }

        private function generatequery(string $type, string $tb_name, array $params, array $conditions): string
        {
            $res = [];
            if (count($conditions) > 0) {
                $newcond = [];
                foreach ($conditions as $key => $value) {
                    $key = $this->mysqli_connection->real_escape_string(strval($key));
                    $value = $this->mysqli_connection->real_escape_string(strval($value));
                    if (strpos($key, "CONTAINS") !== false) {
                        $key = str_replace("CONTAINS","",$key);
                        $newcond[] = <<<body
$key LIKE '%{$value}%'
body;

                    } else if (strpos($key, "LENGTH") !== false) {
                        $key = str_replace("LENGTH","",$key);
                        $newcond[] = <<<body
LENGTH($key) = $value
body;
                    } else {
                        $newcond[] = <<<body
`{$key}`="{$value}"
body;

                    }
                }
                $conditions = $newcond;
                $conditions = implode(" and ", $conditions);
            }
            switch ($type) {
                case "update":
                    $res[] = <<<body
UPDATE `{$tb_name}` SET
body;
                    $paramslist = [];
                    foreach ($params as $key => $value) {
                        $key = $this->mysqli_connection->real_escape_string(strval($key));
                        $value = $this->mysqli_connection->real_escape_string(strval($value));
                        $paramslist[] = <<<body
`{$key}`="{$value}"
body;
                    }
                    $paramslist = implode(",", $paramslist);
                    $res[] = $paramslist;
                    if (gettype($conditions) == gettype("")) {
                        $res[] = "WHERE";
                        $res[] = $conditions;
                    }
                    break;
                case "insert":
                    $res[] = <<<body
INSERT INTO `{$tb_name}`
body;
                    $paramslist = [];
                    $valueslist = [];
                    foreach ($params as $key => $value) {
                        $key = $this->mysqli_connection->real_escape_string(strval($key));
                        $value = $this->mysqli_connection->real_escape_string(strval($value));
                        $paramslist[] = <<<body
`{$key}`
body;
                        $valueslist[] = <<<body
"{$value}"
body;
                    }
                    $paramslist = implode(",", $paramslist);
                    $valueslist = implode(",", $valueslist);
                    $res[] = <<<body
({$paramslist})
body;
                    $res[] = "VALUES";
                    $res[] = <<<body
({$valueslist})
body;
                    break;
                case "delete":
                    $res[] = <<<body
DELETE FROM `{$tb_name}`
body;
                    if (gettype($conditions) == gettype("")) {
                        $res[] = "WHERE";
                        $res[] = $conditions;
                    }
                    break;
                case "select":
                    $res[] = <<<body
SELECT * FROM `{$tb_name}`
body;
                    if (gettype($conditions) == gettype("")) {
                        $res[] = "WHERE";
                        $res[] = $conditions;
                    }
                    break;
                default:
                    $res[] = "invalid";
                    break;
            }
            return implode("\n", $res);
        }

    }
}
?>