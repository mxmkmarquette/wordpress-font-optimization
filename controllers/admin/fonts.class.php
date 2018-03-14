<?php
namespace O10n;

/**
 * Web Font Optimization Admin Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminFonts extends ModuleAdminController implements Module_Admin_Controller_Interface
{

    // admin base
    protected $admin_base = 'themes.php';

    // tab menu
    protected $tabs = array(
        'intro' => array(
            'title' => '<span class="dashicons dashicons-admin-home"></span>',
            'title_attr' => 'Intro'
        ),
        'optimization' => array(
            'title' => 'Font Optimization',
            'title_attr' => 'Web Font Optimization'
        ),
        'google' => array(
            'title' => 'Google Fonts',
            'title_attr' => 'Google Font Downloader',
            'is_tab_of' => ''
        ),
        'settings' => array(
            'title' => 'Settings'
        )
    );

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
            'AdminView',
            'options',
            'file'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        
        // settings link on plugin index
        add_filter('plugin_action_links_' . $this->core->modules('fonts')->basename(), array($this, 'settings_link'));

        // meta links on plugin index
        add_filter('plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2);

        // title on plugin index
        add_action('pre_current_active_plugins', array( $this, 'plugin_title'), 10);

        // admin options page
        add_action('admin_menu', array($this, 'admin_menu'), 50);

        // register activate / deactivate hooks.
        register_activation_hook($this->core->modules('fonts')->basename(), array( $this, 'activate' ));
        register_deactivation_hook($this->core->modules('fonts')->basename(), array( $this, 'deactivate' ));

        $critical_css_files = $this->options->get('css.critical.files');
        if (is_array($critical_css_files) && isset($critical_css_files['webfonts.css'])) {
            $themedir = $this->file->theme_directory(array('critical-css'));
            if (file_exists($themedir . 'webfonts.css')) {
                $this->tabs['critical-css'] = array(
                    'title' => 'Critical CSS',
                    'href' => add_query_arg(array('page' => 'o10n-css-editor','file' => 'critical-css/webfonts.css'), admin_url('admin.php'))
                );
            }
        }
    }
    
    /**
     * Admin menu option
     */
    final public function admin_menu()
    {
        global $submenu;

        // WPO plugin or more than 1 optimization module, add to optimization menu
        if (defined('O10N_WPO_VERSION') || count($this->core->modules()) > 1) {
            add_submenu_page('o10n', __('Web Font Optimization', 'o10n'), __('Web Fonts', 'o10n'), 'manage_options', 'o10n-fonts', array(
                 &$this->AdminView,
                 'display'
             ));

            // change base to admin.php
            $this->admin_base = 'admin.php';
        } else {

            // add menu entry to themes page
            add_submenu_page('themes.php', __('Web Font Optimization', 'o10n'), __('Web Font Optimization', 'o10n'), 'manage_options', 'o10n-fonts', array(
                 &$this->AdminView,
                 'display'
             ));
        }
    }

    /**
     * Settings link on plugin overview.
     *
     * @param  array $links Plugin settings links.
     * @return array Modified plugin settings links.
     */
    final public function settings_link($links)
    {
        $settings_link = '<a href="'.esc_url(add_query_arg(array('page' => 'o10n-fonts','tab' => 'optimization'), admin_url($this->admin_base))).'">'.__('Settings').'</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Show row meta on the plugin screen.
     */
    final public function plugin_row_meta($links, $file)
    {
        if ($file == $this->core->modules('fonts')->basename()) {
            $lgcode = strtolower(get_locale());
            if (strpos($lgcode, '_') !== false) {
                $lgparts = explode('_', $lgcode);
                $lgcode = $lgparts[0];
            }
            if ($lgcode === 'en') {
                $lgcode = '';
            }

            $row_meta = array(
                /*'o10n_scores' => '<a href="' . esc_url('https://optimization.team/pro/') . '" target="_blank" title="' . esc_attr(__('View Google PageSpeed Scores Documentation', 'o10n')) . '" style="font-weight:bold;color:black;">' . __('Upgrade to <span class="g100" style="padding:0px 4px;">PRO</span>', 'o10n') . '</a>'*/
            );

            return array_merge($links, $row_meta);
        }

        return (array) $links;
    }

    /**
     * Plugin title modification
     */
    public function plugin_title()
    {
        ?><script>jQuery(function($){var r=$('*[data-plugin="<?php print $this->core->modules('fonts')->basename(); ?>"]');
            $('.plugin-title strong',r).html('<?php print $this->core->modules('fonts')->name(); ?><a href="https://github.com/o10n-x/" class="g100" target="_blank" rel="noopener">O10N</span>');
});</script><?php
    }
}
