<?php
namespace O10n;

/**
 * Admin Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Admin extends Controller implements Controller_Interface
{
    private $controllers = array(); // admin controllers
    private $is_admin = null;

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {

        // instantiate controller
        return parent::construct($Core);
    }

    /**
     * Setup controller
     */
    protected function setup()
    {

        // WordPress setup
        add_action('wp', array($this,'wp_setup'), $this->first_priority);
    }

    /**
     * WordPress setup hook
     */
    final public function wp_setup()
    {
        // User is administrator with permission to modify optimization settings
        $this->is_admin = current_user_can('manage_options');
        if (!$this->is_admin) {
            // @todo dev IPs
        }
    }

    /**
     * User is administrator with permission to modify optimization settings
     */
    final public function is_admin()
    {
        return $this->is_admin;
    }
}
