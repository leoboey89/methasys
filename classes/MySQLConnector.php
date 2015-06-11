<?php
class MySQLConnector
{
    protected $_connection;
    protected $_result;
    protected $_tableExist;


    public function __construct($host, $user, $pwd, $db)
    {
        $this->_connection = @new mysqli($host, $user, $pwd, $db);
        if (mysqli_connect_errno()) {
            throw new RuntimeException('Cannot access database: ' . mysqli_connect_error());
        }
    }

    public function getResultSet($table, $fieldName = null, $dataFilter = null, $groupBy = null, $orderBy = null)
    {
        if (is_array($table)) {
            for ($tableCount = 0; $tableCount < count($table); $tableCount++) {
                $this->checkTableExistence($table[$tableCount]);
                if (!$this->_tableExist) {
                    throw new RuntimeException('No such table "' . $table[$tableCount] . '". Please check table name.');
                }
                if (!$tableCount) {
                    $tableName = $table[$tableCount];
                } else {
                    $tableName = $tableName . ', ' . $table[$tableCount];
                }
            }
        } else {
            $this->checkTableExistence($table);
            if (!$this->_tableExist) {
                throw new RuntimeException('No such table "' . $table . '". Please check table name.');
            }
            $tableName = $table;
        }

        if (is_null($fieldName)) {
            $fieldName = ['*'];
        }

        if (is_array($fieldName)) {
            for ($fieldNameCount = 0; $fieldNameCount < count($fieldName); $fieldNameCount++) {
                if (!$fieldNameCount) {
                    $tableField = $fieldName[$fieldNameCount];
                } else {
                    $tableField = $tableField . ', ' . $fieldName[$fieldNameCount];
                }
            }
        } else {
            $tableField = $fieldName;
        }

        if (!is_null($dataFilter)) {
            if (is_array($dataFilter)) {
                for ($dataFilterCount = 0; $dataFilterCount < count($dataFilter); $dataFilterCount++) {
                    if (!$dataFilterCount) {
                        $tableFilter = $dataFilter[$dataFilterCount];
                    } else {
                        $tableFilter = $tableFilter . ' AND ' . $dataFilter[$dataFilterCount];
                    }
                }
            } else {
                $tableFilter = $dataFilter;
            }
        }

        if (!is_null($groupBy)) {
            if (is_array($groupBy)) {
                for ($groupByCount = 0; $groupByCount < count($groupBy); $groupByCount++) {
                    if (!$groupByCount) {
                        $tableGroupBy = $groupBy[$groupByCount];
                    } else {
                        $tableGroupBy = $tableGroupBy . ', ' . $groupBy[$groupByCount];
                    }
                }
            } else {
                $tableGroupBy = $groupBy;
            }
        }

        if (!is_null($orderBy)) {
            if (is_array($groupBy)) {
                for ($orderByCount = 0; $orderByCount < count($orderBy); $orderByCount++) {
                    if (!$orderByCount) {
                        $tableOrderBy = $orderBy[$orderByCount];
                    } else {
                        $tableOrderBy = $tableOrderBy . ', ' . $orderBy[$orderByCount];
                    }
                }
            } else {
                $tableOrderBy = $orderBy;
            }
        }

        if (is_null($dataFilter) && is_null($groupBy) && is_null($orderBy)) {
            $sql = "SELECT $tableField FROM $tableName";
        } else if (is_null($dataFilter) && is_null($groupBy) && !is_null($orderBy)){
            $sql = "SELECT $tableField FROM $tableName ORDER BY $tableOrderBy";
        } else if (is_null($dataFilter) && !is_null($groupBy) && is_null($orderBy)){
            $sql = "SELECT $tableField FROM $tableName GROUP BY $tableGroupBy";
        } else if (is_null($dataFilter) && !is_null($groupBy) && !is_null($orderBy)){
            $sql = "SELECT $tableField FROM $tableName GROUP BY $tableGroupBy ORDER BY $tableOrderBy";
        } else if (!is_null($dataFilter) && is_null($groupBy) && is_null($orderBy)){
            $sql = "SELECT $tableField FROM $tableName WHERE $tableFilter";
        } else if (!is_null($dataFilter) && is_null($groupBy) && !is_null($orderBy)){
            $sql = "SELECT $tableField FROM $tableName WHERE $tableFilter ORDER BY $tableOrderBy";
        } else if (!is_null($dataFilter) && !is_null($groupBy) && is_null($orderBy)){
            $sql = "SELECT $tableField FROM $tableName WHERE $tableFilter GROUP BY $tableGroupBy";
        } else if (!is_null($dataFilter) && !is_null($groupBy) && !is_null($orderBy)){
            $sql = "SELECT $tableField FROM $tableName WHERE $tableFilter GROUP BY $tableGroupBy ORDER BY $tableOrderBy";
        }

        if(!$this->_results = $this->_connection->query($sql)) {
            throw new RunTimeException($this->_connection->error . '. Please check submitted query ' . $sql);
        }

        return $this->_results;
    }

    public function getResultBySQL($sql) {
        if(!$this->_results = $this->_connection->query($sql)) {
            throw new RunTimeException($this->_connection->error . '. Please check submitted query ' . $sql);
        }

        return $this->_results;
    }

    public function isExist($table, $dataFilter = null)
    {
        if (is_array($table)) {
            for ($tableCount = 0; $tableCount < count($table); $tableCount++) {
                $this->checkTableExistence($table[$tableCount]);
                if (!$this->_tableExist) {
                    throw new RuntimeException('No such table "' . $table[$tableCount] . '". Please check table name.');
                }
                if (!$tableCount) {
                    $tableName = $table[$tableCount];
                } else {
                    $tableName = $tableName . ', ' . $table[$tableCount];
                }
            }
        } else {
            $this->checkTableExistence($table);
            if (!$this->_tableExist) {
                throw new RuntimeException('No such table "' . $table . '". Please check table name.');
            }
            $tableName = $table;
        }

        if (!is_null($dataFilter)) {
            if (is_array($dataFilter)) {
                for ($dataFilterCount = 0; $dataFilterCount < count($dataFilter); $dataFilterCount++) {
                    if (!$dataFilterCount) {
                        $tableFilter = $dataFilter[$dataFilterCount];
                    } else {
                        $tableFilter = $tableFilter . ' AND ' . $dataFilter[$dataFilterCount];
                    }
                }
            } else {
                $tableFilter = $dataFilter;
            }
        }

        if (is_null($dataFilter)) {
            $sql = "SELECT * FROM $tableName";
        } else {
            $sql = "SELECT * FROM $tableName WHERE $tableFilter";
        }

        if(!$this->_results = $this->_connection->query($sql)) {
            throw new RunTimeException($this->_connection->error . '. Please check submitted query ' . $sql);
        }

        if($this->_results->num_rows == 0) {
            return false;
        } else {
            return true;
        }

    }

    public function getResultSetBySQL($sql)
    {
        // $sql = "SELECT $tableField FROM $tableName WHERE $tableFilter";

        if(!$this->_results = $this->_connection->query($sql)) {
            throw new RunTimeException($this->_connection->error . '. Please check submitted query ' . $sql);
        }
        return $this->_results;
    }

    public function __destruct()
    {
        $this->_connection->close();
    }

    public function realEscapeString($inputString) 
    {  
        return $this->_connection->real_escape_string($inputString);
    }

    private function checkTableExistence($tableName)
    {
        $this->_tableExist = false;
        $this->_tableExist = $this->_connection->query('SELECT * FROM ' . $tableName);
        if(!$this->_tableExist) {
            return $this->_tableExist = false;
        } else {
            return $this->_tableExist = true;
        }
    }

    public function insertData($tableName, $fieldName = array(), $fieldValue = array()) 
    {
        if (!isset($tableName)) {
            throw new Exception('Please make sure TABLE NAME argument is set.');
        }
        else if (empty($fieldName) || empty($fieldValue)) {
            throw new Exception('FIELD NAME and FIELD VALUE array cannot be empty.');
        } else if (count($fieldName) != count($fieldValue)) {
            throw new Exception('Array count variant for FIELD NAME and FIELD VALUE arrays.');
        }

        for ($fieldNameCount = 0; $fieldNameCount < count($fieldName); $fieldNameCount++) {
            if (!$fieldNameCount) {
                $tableField = $fieldName[$fieldNameCount];
            } else {
                $tableField = $tableField . ', ' . $fieldName[$fieldNameCount];
            }
        }    

        for ($fieldValueCount = 0; $fieldValueCount < count($fieldValue); $fieldValueCount++) {
            if (!$fieldValueCount) {
                $fieldData = "'$fieldValue[$fieldValueCount]'";
            } else {
                $fieldData = $fieldData. ', ' . "'$fieldValue[$fieldValueCount]'";
            }
        }

        $sql = "INSERT INTO $tableName($tableField) VALUES ($fieldData)";

        if ($this->_connection->query($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public function updateData($tableName, $setValue = array(), $dataFilter = array()) 
    {
        if (!isset($tableName)) {
            throw new Exception('Please make sure TABLE NAME argument is set.');
        }
        else if (empty($setValue) || empty($dataFilter)) {
            throw new Exception('SET VALUE and FILTER FIELD array cannot be empty.');
        } 

        for ($setValueCount = 0; $setValueCount < count($setValue); $setValueCount++) {
            if (!$setValueCount) {
                $tableSetValue = $setValue[$setValueCount];
            } else {
                $tableSetValue = $tableSetValue . ', ' . $setValue[$setValueCount];
            }
        }    

        for ($dataFilterCount = 0; $dataFilterCount < count($dataFilter); $dataFilterCount++) {
            if (!$dataFilterCount) {
                $tableDataFilter = $dataFilter[$dataFilterCount];
            } else {
                $tableDataFilter = $tableDataFilter. ' and ' . $dataFilter[$dataFilterCount];
            }
        }

        $sql = "UPDATE $tableName SET $tableSetValue WHERE $tableDataFilter";

        if ($this->_connection->query($sql)) {
            return true;
        } else {
            return false;
        }
    }
}
?>
