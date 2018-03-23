<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/*EXTRA FUNCTIONS: mysqli\_.+*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_database_drivers
 */

require_code('database/shared/mysql');

/**
 * Database Driver.
 *
 * @package    core_database_drivers
 */
class Database_Static_mysqli extends Database_super_mysql
{
    protected $cache_db = array();
    public $last_select_db = null;
    public $reconnected_once = false;

    /**
     * Get a database connection. This function shouldn't be used by you, as a connection to the database is established automatically.
     *
     * @param  boolean $persistent Whether to create a persistent connection
     * @param  string $db_name The database name
     * @param  string $db_host The database host (the server)
     * @param  string $db_user The database connection username
     * @param  string $db_password The database connection password
     * @param  boolean $fail_ok Whether to on error echo an error and return with a null, rather than giving a critical error
     * @return ?array A database connection (note for MySQL, it's actually a pair, containing the database name too: because we need to select the name before each query on the connection) (null: error)
     */
    public function get_connection($persistent, $db_name, $db_host, $db_user, $db_password, $fail_ok = false)
    {
        if (!function_exists('mysqli_connect')) {
            $error = 'MySQLi not on server (anymore?). Try using the \'mysql\' database driver. To use it, edit the _config.php config file.';
            if ($fail_ok) {
                echo ((running_script('install')) && (get_param_string('type', '') == 'ajax_db_details')) ? strip_html($error) : $error;
                return null;
            }
            critical_error('PASSON', $error);
        }

        // Potential caching
        $x = serialize(array($db_name, $db_host));
        if (array_key_exists($x, $this->cache_db)) {
            if ($this->last_select_db[1] !== $db_name) {
                mysqli_select_db($this->cache_db[$x], $db_name);
                $this->last_select_db = array($this->cache_db[$x], $db_name);
            }

            return array($this->cache_db[$x], $db_name);
        }
        $db_port = 3306;
        if (strpos($db_host, ':') !== false) {
            list($db_host, $_db_port) = explode(':', $db_host);
            $db_port = intval($_db_port);
        }
        $db_link = @mysqli_connect(($persistent ? 'p:' : '') . $db_host, $db_user, $db_password, '', $db_port);

        if ($db_link === false) {
            $error = 'Could not connect to database-server (when authenticating) (' . mysqli_connect_error() . ')';
            if ($fail_ok) {
                echo ((running_script('install')) && (get_param_string('type', '') == 'ajax_db_details')) ? strip_html($error) : $error;
                return null;
            }
            critical_error('PASSON', $error);
        }
        if (!mysqli_select_db($db_link, $db_name)) {
            if ($db_user == 'root') {
                @mysqli_query($db_link, 'CREATE DATABASE IF NOT EXISTS ' . $db_name);
            }

            if (!mysqli_select_db($db_link, $db_name)) {
                $error = 'Could not connect to database (' . mysqli_error($db_link) . ')';
                if ($fail_ok) {
                    echo $error . "\n";
                    return null;
                }
                critical_error('PASSON', $error); //warn_exit(do_lang_tempcode('CONNECT_ERROR'));
            }
        }
        $this->last_select_db = array($db_link, $db_name);

        $this->cache_db[$x] = $db_link;

        $init_queries = $this->get_init_queries();
        foreach ($init_queries as $init_query) {
            @mysqli_query($db_link, $init_query);
        }

        global $SITE_INFO;
        $test = @mysqli_set_charset($db_link, $SITE_INFO['database_charset']);
        if ((!$test) && ($SITE_INFO['database_charset'] == 'utf8mb4')) {
            // Conflict between compiled-in MySQL client library and what the server supports
            $test = @mysqli_set_charset($db_link, 'utf8');
            @mysqli_query($db_link, 'SET NAMES "' . addslashes('utf8mb4') . '"');
        }

        return array($db_link, $db_name);
    }

    /**
     * This function is a very basic query executor. It shouldn't usually be used by you, as there are abstracted versions available.
     *
     * @param  string $query The complete SQL query
     * @param  mixed $connection The DB connection
     * @param  ?integer $max The maximum number of rows to affect (null: no limit)
     * @param  integer $start The start row to affect
     * @param  boolean $fail_ok Whether to output an error on failure
     * @param  boolean $get_insert_id Whether to get the autoincrement ID created for an insert query
     * @return ?mixed The results (null: no results), or the insert ID
     */
    public function query($query, $connection, $max = null, $start = 0, $fail_ok = false, $get_insert_id = false)
    {
        list($db_link, $db_name) = $connection;

        if (!$this->query_may_run($query, $connection, $get_insert_id)) {
            return null;
        }

        if ($this->last_select_db[1] !== $db_name) {
            mysqli_select_db($db_link, $db_name);
            $this->last_select_db = array($db_link, $db_name);
        }

        $this->apply_sql_limit_clause($query, $max, $start);

        $results = @mysqli_query($db_link, $query);
        if (($results === false) && ((!$fail_ok) || (strpos(mysqli_error($db_link), 'is marked as crashed and should be repaired') !== false))) {
            $err = mysqli_error($db_link);

            if ((function_exists('mysqli_ping')) && ($err == 'MySQL server has gone away') && (!$this->reconnected_once)) {
                cms_ini_set('mysqli.reconnect', '1');
                $this->reconnected_once = true;
                mysqli_ping($db_link);
                $ret = $this->query($query, $connection, null/*already encoded*/, null/*already encoded*/, $fail_ok, $get_insert_id);
                $this->reconnected_once = false;
                return $ret;
            }

            $this->handle_failed_query($query, $err, $connection);
            return null;
        }

        $sub = substr(ltrim($query), 0, 4);
        if (($results !== true) && (($sub === '(SEL') || ($sub === 'SELE') || ($sub === 'sele') || ($sub === 'CHEC') || ($sub === 'EXPL') || ($sub === 'REPA') || ($sub === 'DESC') || ($sub === 'SHOW')) && ($results !== false)) {
            return $this->get_query_rows($results, $query, $start);
        }

        if ($get_insert_id) {
            if (strtoupper(substr(ltrim($query), 0, 7)) === 'UPDATE ') {
                return mysqli_affected_rows($db_link);
            }
            $ins = mysqli_insert_id($db_link);
            if ($ins === 0) {
                $table = substr($query, 12, strpos($query, ' ', 12) - 12);
                $rows = $this->query('SELECT MAX(id) AS x FROM ' . $table, $db_link, 1, 0, false, false);
                return $rows[0]['x'];
            }
            return $ins;
        }

        return null;
    }

    /**
     * Get the rows returned from a SELECT query.
     *
     * @param  resource $results The query result pointer
     * @param  string $query The complete SQL query (useful for debugging)
     * @param  integer $start Where to start reading from
     * @return array A list of row maps
     */
    protected function get_query_rows($results, $query, $start)
    {
        $num_fields = mysqli_num_fields($results);
        $names = array();
        $types = array();
        for ($x = 0; $x < $num_fields; $x++) {
            $field = mysqli_fetch_field($results);
            $names[$x] = $field->name;
            $types[$x] = $field->type;
        }

        $out = array();
        $newrow = array();
        while (($row = mysqli_fetch_row($results)) !== null) {
            $j = 0;
            foreach ($row as $v) {
                $name = $names[$j];
                $type = $types[$j];

                if (is_string($type)) {
                    if (substr($type, 0, 3) == 'int') {
                        $type = 'int';
                    }
                }

                if (($type === 'int') || ($type === 1) || ($type === 2) || ($type === 3) || ($type === 8) || ($type === 9)) {
                    if ((($v === null)) || ($v === '')) {
                        $newrow[$name] = null;
                    } else {
                        $newrow[$name] = intval($v);
                    }
                } elseif (($type === 'decimal') || ($type === 'real') || ($type === 4) || ($type === 5) || ($type === 246)) {
                    if ((($v === null)) || ($v === '')) {
                        $newrow[$name] = null;
                    } else {
                        $newrow[$name] = floatval($v);
                    }
                } elseif (($type === 16) || ($type === 'bit')) {
                    if ((strlen($v) === 1) && (ord($v[0]) <= 1)) {
                        $newrow[$name] = ord($v); // 0/1 char for BIT field
                    } else {
                        $newrow[$name] = intval($v);
                    }
                } else {
                    $newrow[$name] = $v;
                }

                $j++;
            }

            $out[] = $newrow;
        }
        mysqli_free_result($results);

        return $out;
    }

    /**
     * Escape a string so it may be inserted into a query. If SQL statements are being built up and passed using db_query then it is essential that this is used for security reasons. Otherwise, the abstraction layer deals with the situation.
     *
     * @param  string $string The string
     * @return string The escaped string
     */
    public function escape_string($string)
    {
        if (function_exists('ctype_alnum')) {
            if (ctype_alnum($string)) {
                return $string; // No non-trivial characters
            }
        }
        if (preg_match('#[^a-zA-Z0-9\.]#', $string) === 0) {
            return $string; // No non-trivial characters
        }

        $string = fix_bad_unicode($string);

        if ($this->last_select_db === null) {
            return addslashes($string);
        }
        return mysqli_real_escape_string($this->last_select_db[0], $string);
    }
}
