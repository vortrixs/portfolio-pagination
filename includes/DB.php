<?php

/**
 * Class for managing the database connection
 *
 * @author Alex Garret
 * @link https://www.youtube.com/playlist?list=PLfdtiltiRHWF5Rhuk7k4UAU1_yLAZzhWc Tutorial videos from Alex Garret (phpacademy)
 * @author Hans Erik Jepsen <hanserikjepsen@hotmail.com>
 */

class DB {

    private static $_instance = null;
    public $_pdo,
            $_query,
            $_error = false,
            $_results,
            $_first,
            $_count = 0;

    /**
     * Connects to the database with PDO
     * Config into loaded from init.php and defined in config.ini
     */
    public function __construct($host = DB_HOST, $database = DB_NAME, $username = DB_USERNAME, $password = DB_PASSWORD) {
        try {
            $this->_pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
            $this->_pdo->exec('set names utf8');
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * Method for sending queries to the database
     * 
     * @param string $sql
     * @param array $params
     * @param array $searchParams
     * @param int $searchTermsCount
     * @return object
     */
    public function query($sql, $params = array(), $searchParams = array(), $searchTermsCount = null) {
        $this->_error = false;
        $prepare = $this->_query = $this->_pdo->prepare($sql);

        if (isset($prepare)) {
            $x = 1;
            if (count($params)) {
                foreach ($params as $param) {
                    $this->_query->bindValue($x, $param);
                    $x++;
                }
            }

            if (!empty($searchParams) && !empty($searchTermsCount)) {
                $z = 1;
                for ($x = 0; $x < $searchTermsCount; $x++) {
                    for ($y = 0; $y < count($searchParams); $y++) {
                        var_dump($z);
                        $this->_query->bindValue($z++, $searchParams[$y]);
                    }
                }
            }

            if ($this->_query->execute()) {
                $this->_results = $this->_query->fetchAll(\PDO::FETCH_OBJ);
                $this->_count = $this->_query->rowCount();
            } else {
                $this->_error = true;
            }
        }
        return $this;
    }

    /**
     * Method for dynamically generating SQL queries
     *
     * @param string $action SQL statement
     * @param string $table Database table
     * @param array $where Multi-dimensional array for multiple WHERE statements
     * @param array $options Array with miscellaneous satements like ORDER BY
     * @return boolean|object
     */
    public function action($action, $table, $where = array(), $options = array()) {
        $sql = "{$action} FROM {$table}";

        if (!empty($where)) {
            $sql .= " WHERE ";
            foreach ($where as $clause) {
                if (count($clause) === 3) {
                    $operators = array('=', '>', '<', ' >=', '<=');

                    if (isset($clause)) {
                        $field = $clause[0];
                        $operator = $clause[1];
                        $value[] = $clause[2];
                        $bindValue = '?';
                    }

                    if (in_array($operator, $operators)) {
                        $sql .= "{$field} {$operator} {$bindValue}";
                        $sql .= " AND ";
                    }
                }
            }
            $sql = rtrim($sql, " AND ");
        }
        if (!empty($options)) {
            foreach ($options as $optionKey => $optionValue) {
                $sql .= " {$optionKey} {$optionValue}";
            }
        }

        if (!$this->query($sql, $value)->error()) {
            return $this;
        }
        return false;
    }

    /**
     * Execute a SQL SELECT statement
     * 
     * @param array $select Array containing the names of the columns to select
     * @param string $table Database table
     * @param array $where Multi-dimensional array for multiple WHERE statements
     * @param array $options Array with miscellaneous satements like ORDER BY
     * @return boolean|object
     */
    public function get($select = array(), $table, $where = array(), $options = null) {
        return $this->action('SELECT ' . implode($select, ', '), $table, $where, $options);
    }

    /**
     * Execute a SQL DELETE statement
     * 
     * @param string $table Database table
     * @param array $where Multi-dimensional array for multiple WHERE statements
     * @return boolean|object
     */
    public function delete($table, $where = array()) {
        return $this->action('DELETE', $table, $where);
    }

    /**
     * Execute a SQL INSERT INTO statement
     * 
     * @param string $table Database table
     * @param array $fields Array of colums and value to insert
     * @return boolean
     */
    public function insert($table, $fields = array()) {
        $keys = array_keys($fields);
        $values = '';
        $x = 1;

        foreach ($fields as $field) {
            $values .= '?';
            if ($x < count($fields)) {
                $values .= ', ';
            }
            $x++;
        }

        $sql = "INSERT INTO {$table} (`" . implode('`,`', $keys) . "`) VALUES ({$values})";

        if (!$this->query($sql, $fields)->error()) {
            return true;
        }
        return false;
    }

    /**
     * Execute a SQL UPDATE statement
     * 
     * @param string $table Database table
     * @param int $id The id of the row to update
     * @param array $fields Array with column names and the updated values
     * @return boolean
     */
    public function update($table, $id, $fields = array()) {
        $set = '';
        $x = 1;

        foreach ($fields as $name => $value) {
            $set .= "{$name} = ?";
            if ($x < count($fields)) {
                $set .= ', ';
            }
            $x++;
        }

        $sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";

        if (!$this->query($sql, $fields)->error()) {
            return true;
        }
        return false;
    }

    /**
     * Shows results from the get method
     * 
     * @return object
     */
    public function results() {
        return $this->_results;
    }

    /**
     * Returns only the first result from the get method
     * 
     * @return object
     */
    public function first() {
        $this->_first = $this->results();
        return $this->_first[0];
    }

    /**
     * Used for error checking
     * 
     * Return true if there is an error or false if there are no errors during the query method
     * 
     * @return boolean Returns true||false
     */
    public function error() {
        return $this->_error;
    }

    /**
     * Shows the number of row in the results from the get method
     * 
     * @return int
     */
    public function count() {
        return $this->_count;
    }

}
