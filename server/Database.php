<?php

namespace server;

interface Queries
{
    /**
     * Establish the database connection.
     */
    public function getConnection();

    /**
     * Set the table to operate on.
     */
    public function table($table);

    /**
     * Set the data/values for an insert or update query.
     */
    public function setValues(array $data);

    /**
     * Execute the query based on the type (select, insert, update, delete).
     */
    public function execute($type);

    /**
     * Add where conditions for the query.
     */
    public function where($field, $value = null);

    /**
     * Set the columns to select or fetch.
     */
    public function setColumn($columns);

    /**
     * Add an ORDER BY clause to the query.
     */
    public function orderBy($columns, $sort = 'ASC');

    /**
     * Add a GROUP BY clause to the query.
     */
    public function groupBy($columns);

    /**
     * Add a LIMIT clause to the query.
     */
    public function limit($count, $offset = null);

    /**
     * Display an array in a readable format.
     */
    public static function display($array);

    /**
     * Execute a raw SQL query and fetch results.
     */
    public static function fetchSql($sql, int $type);

}

class Database implements Queries {
    private $DBName = "";
    private $DBServer = "";
    private $DBUser = "";
    private $DBPass = "";

    public $db;
    protected $tableName;
    protected $data = [];
    protected $fields = [];
    private $generatedQuery;
    private $whereClauses = [];
    private $results = [];
    private $column = "*";

    private $orderBy = '';
    private $groupBy = '';
    private $limit = '';
    private $strict = false;

    public function __construct()
    {
        $this->loadEnv();
        $this->DBServer = getenv('DB_SERVER');
        $this->DBUser = getenv('DB_USER');
        $this->DBPass = getenv('DB_PASS');
        $this->DBName = getenv('DB_NAME');
        $this->getConnection();

        $this->db->set_charset("utf8mb4");
    }
    
    /**
     * Load environment variables from a .env file.
     */
    private function loadEnv()
    {
        $envFile ='.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                // Ignore comments and empty lines
                if (strpos($line, '#') === 0 || trim($line) === '') {
                    continue;
                }

                // Split the line into key-value pairs and trim extra whitespace
                list($key, $value) = explode('=', $line, 2);

                // Ensure both key and value are trimmed and treated as strings
                $key = trim($key);
                $value = trim($value);

                // Set the environment variables
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        } else {
            echo "Error: .env file not found!";
        }
    }


    /**
     * Establish the database connection.
     */
    public function getConnection()
    {
        $this->db = new \mysqli($this->DBServer, $this->DBUser, $this->DBPass, $this->DBName);
        $this->db->set_charset("utf8");
        if ($this->db->connect_error) {
            trigger_error('Database connection failed: '  . $this->db->connect_error, E_USER_ERROR);
        }
    }

    public function getDB()
    {
        return $this->db;
    }

    public static function display($array)
    {
        echo "<pre>" . print_r($array, true) . "</pre>";
    }

    public static function fetchSql($sql, int $type = 0)
    {
        $db = new self(); // Create an instance of the Database class.
        $db->getConnection(); // Make sure you establish a database connection.
        $db->db->set_charset("utf8mb4");

        // Execute the SQL query.
        $result = $db->db->query($sql); // Access the db property through the instance.

        if ($result === false) {
            throw new \Exception("SQL Error: " . $sql . $db->db->error); // Access the db property through the instance.
        }

        if ($type == 1) {
            // Return the number of rows in the result set.
            return $result->num_rows;
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = (object) $row;
        }
     
        return $data;
    }

    /**
     * Set the table to operate on for the query.
     *
     * This method initializes the table name and resets any previously set fields, data, 
     * or where clauses. It allows chaining with other methods to construct queries.
     *
     * Example Usage:
     * ```php
     * $db->table('users')->setColumn('name, email')->execute('select');
     * // Generates: SELECT name, email FROM users
     *
     * $db->table('products')->where('price', 100)->execute('select');
     * // Generates: SELECT * FROM products WHERE price = 100
     *
     * $db->table('orders')->where('status', 'completed')->orderBy('created_at', 'DESC')->limit(10)->execute('select');
     * // Generates: SELECT * FROM orders WHERE status = 'completed' ORDER BY created_at DESC LIMIT 10
     *
     * $db->table('users')->setValues(['name' => 'John Doe', 'email' => 'john@example.com'])->execute('insert');
     * // Generates: INSERT INTO users (name, email) VALUES ('John Doe', 'john@example.com')
     *
     * $db->table('users')->setValues(['email' => 'new_email@example.com'])->where('id', 1)->execute('update');
     * // Generates: UPDATE users SET email = 'new_email@example.com' WHERE id = 1
     * ```
     *
     * @param string $table The name of the table to operate on.
     * @return self Returns the instance for method chaining.
     */
    public function table($table)
    {
        $this->fields = $this->data = $this->whereClauses = [];
        $this->tableName = $table;
        return $this;
    }

    public function setValues(array $data)
    {    
        $this->data = $data;
        $this->fields = array_keys($data);
        return $this;
    }

    public function setColumn($columns)
    {
        $this->column  = $columns;
        return $this;
    }

    public function where($field, $value = null)
    {
        if (is_array($field)) {
            $values = implode(', ', array_map(function ($val) {
                return is_numeric($val) ?
                  $this->db->real_escape_string($val):
                 "'" . $this->db->real_escape_string($val) . "'";
               
            }, $field));
            $this->whereClauses[] = "$field IN ($values)";
        } else {
            if (is_array($value)) {
                $values = implode(', ', array_map(function ($val) {
                    return is_numeric($val) ?
                        $val :
                        "'" . $val. "'";
                }, $value));
                $this->whereClauses[] = "$field IN ($values)";
            } elseif (is_numeric($value)) {
                $this->whereClauses[] = "$field = $value";
            } else {
                $whereValue = $this->strict ? "{$this->db->real_escape_string($value)}" : "%{$this->db->real_escape_string($value)}%" ;
                $this->whereClauses[] = "$field LIKE '$whereValue'";
            }
        }

        return $this;
    }

    public function orderBy($columns, $sort = 'ASC')
    {
        $orderBy = is_array($columns) ? implode(', ', $columns): $columns;
        $this->orderBy = " ORDER BY $orderBy $sort";
        return $this;    
    }

    public function groupBy($columns)
    {
        $groupBy = is_array($columns) ? implode(', ', $columns) : $columns;
        $this->groupBy = " GROUP BY $groupBy";
        return $this;
    }

    public function limit($count, $offset = null)
    {
        $this->limit = $offset != NULL ? " LIMIT $offset, $count": " LIMIT $count";
        return $this;
    }

    private function insert()
    {
        $fields = implode(", ", $this->fields);
        $values = "'" . implode("', '", $this->data) . "'";
        $query = "INSERT INTO {$this->tableName} ($fields) VALUES ($values)";

        // $this->generatedQuery = $query;
        return $query;
    }

    /**
     * Get the last inserted ID.
     *
     * @return int|string|null The last inserted ID or null if no insert operation was performed.
     */
    public function lastInsertId()
    {
        return $this->db->insert_id; // Returns the last auto-increment ID
    }

    private function update()
    {
        if (empty($this->data) || empty($this->whereClauses)) {
            return false;
        }

        $setClause = str_replace("0 = '","",$this->buildSetClause());
        $whereClause = implode(" AND ", $this->whereClauses);

        $query = "UPDATE {$this->tableName} SET $setClause WHERE $whereClause";

        // $this->generatedQuery = $query;
        return $query;
    }

    private function delete()
    {
        if ( empty($this->whereClauses)) {
            return false;
        }
        $whereClause = implode(" AND ", $this->whereClauses);

        $query = "DELETE FROM {$this->tableName} WHERE $whereClause";
        return $query;
    }

    private function select()
    {
        $whereClause = (!empty($this->whereClauses)) ? "WHERE " . implode(" AND ", $this->whereClauses) : '';
        $orderByClause = $this->orderBy;
        $groupByClause = $this->groupBy;
        $limitClause = $this->limit;

        $query = "SELECT {$this->column} FROM {$this->tableName} $whereClause $groupByClause $orderByClause $limitClause";
        return $query;

    }

    private function buildSetClause()
    {
        // if(!is_array($this->data)) return $this->data;

        $setClause = [];
        foreach ($this->fields as $field) {
            $setClause[] = "$field = '{$this->data[$field]}'";
        }

        return implode(", ", $setClause);
    }

    public function execute($type)
    {
            
        if (!$this->tableName AND empty($this->data) ) {
            // $this->generatedQuery = $query;
            return false;
        }

        switch ($type) {
            case 'insert':
                $query = $this->insert();
                break;
            case 'update':
                $query = $this->update();
                break;
            case 'delete':
                $query = $this->delete();
                break;
            case 'select':
                $query = $this->select();
                break;
            default:
                return false; // Invalid operation
        }
        $this->generatedQuery = $query;

        $sql = $this->db->query($query);

        if ($sql === false) {
            throw new \Exception("SQL Error: " . $query. $this->db->error);
        }

        // For SELECT queries, fetch the result as an associative array
        if ($type === 'select')
        {
            while ($row = $sql->fetch_assoc()) {
                $results[] = (object) $row;
            }

            // Check if no results were found
            if (count($results) < 1) {
                return "No results found.";
            }

            return $results;
        }

        return $sql; // For other queries, return the SQL result object
    }

    public function getGeneratedQuery()
    {   
        echo $this->generatedQuery;
    }

    /**
     * Set the value of strict
     *
     * @return  self
     */ 
    public function setStrict($strict)
    {
        $this->strict = $strict;

        return $this;
    }
}

?>