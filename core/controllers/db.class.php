<?php
namespace O10n;

/**
 * Database Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Db extends Controller implements Controller_Interface
{
    private $mysqli = false; // use MySQLi?

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {

        // detect MySQLi support
        if (function_exists('mysqli_connect')) {
            if (defined('WP_USE_EXT_MYSQL')) {
                $this->mysqli = ! WP_USE_EXT_MYSQL;
            } elseif (version_compare(phpversion(), '5.5', '>=') || ! function_exists('mysql_connect')) {
                $this->mysqli = true;
            } elseif (false !== strpos($GLOBALS['wp_version'], '-')) {
                $this->mysqli = true;
            }
        }
    }

    /**
     * Execute MySQL query
     *
     * @param  string   $query MySQL query.
     * @return resource MySQL query resource.
     */
    public function query($query)
    {
        if ($this->mysqli) {
            return @\mysqli_query($this->wpdb->dbh, $query);
        } else {
            return @\mysql_query($query, $this->wpdb->dbh);
        }
    }

    /**
     * Number of rows in result
     *
     * @param  resource $result MySQL result resource.
     * @return int      Number of rows.
     */
    public function num_rows($result)
    {
        if ($this->mysqli) {
            return $result->num_rows;
        } else {
            return @\mysql_num_rows($result);
        }
    }

    /**
     * Fetch assoc row from result
     *
     * @param  resource $result MySQL result resource.
     * @return array    Result row.
     */
    public function fetch_assoc($result)
    {
        if ($this->mysqli) {
            return @\mysqli_fetch_assoc($result);
        } else {
            return @\mysqli_fetch_array($result, MYSQL_ASSOC);
        }
    }

    /**
     * Free result
     *
     * @param  resource $result MySQL result resource.
     * @return null
     */
    public function free_result($result)
    {
        if ($this->mysqli) {
            return @\mysqli_free_result($result);
        } else {
            return @\mysql_free_result($result);
        }
    }

    /**
     * Free result
     *
     * @param  string $str String to escape.
     * @return string Escaped string.
     */
    public function escape($str)
    {
        if ($this->mysqli) {
            return @\mysqli_real_escape_string($str, $this->wpdb->dbh);
        } else {
            return @\mysql_real_escape_string($str, $this->wpdb->dbh);
        }
    }
}
