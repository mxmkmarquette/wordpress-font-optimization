<?php
namespace O10n;

/**
 * Web Font Optimization Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Fonts extends Controller implements Controller_Interface
{
    // default linked font removal list
    private $font_removal_list = array(
        'fonts.googleapis.com'
    );

    // module key refereces
    private $client_modules = array(
        'fonts-loadconfig',
        'fonts-fontface',
        'fonts-observer',
        'fonts-gfl',
        'webfontloader'
    );

    // automatically load dependencies
    private $client_module_dependencies = array();

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
            'env',
            'options',
            'client',
            'output'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        // disabled
        if (!$this->env->is_optimization()) {
            return;
        }

        // add module definitions
        $this->client->add_module_definitions($this->client_modules, $this->client_module_dependencies);

        // Font Face API optimization
        if ($this->options->bool('fonts.fontface.enabled')) {
            $this->setup_fontface();
        }

        // Font Face Observer optimization
        if ($this->options->bool('fonts.fontfaceobserver.enabled')) {
            $this->setup_observer();
        }

        // Google Font Loader optimization
        if ($this->options->bool('fonts.googlefontloader.enabled')) {
            $this->setup_gfl();
        }

        // Remove linked fonts
        if ($this->options->bool('fonts.remove_linked.enabled')) {

            // custom filter list
            if ($this->options->bool('fonts.remove_linked.filter.enabled')) {
                $this->font_removal_list = $this->options->get('fonts.remove_linked.filter.list');
            }

            // apply filter to font removal list
            $this->font_removal_list = apply_filters('o10n_fonts_removal_list', $this->font_removal_list);

            // use CSS optimization filters
            if (defined('O10N_CSS_VERSION')) {
                
                // remove linked stylesheets from HTML
                add_filter('o10n_stylesheet_pre', array( $this, 'remove_linked_stylesheet' ), 10, 1);

                // remove @import links from CSS
                add_filter('o10n_proxy_css_content', array( $this, 'remove_linked_import' ), 10, 1);
            } else {

                // use HTML output filter
                add_filter('o10n_html_pre', array( $this, 'remove_linked_html' ), 10, 1);
            }
        }
    }

    /**
     * Setup Font Face API optimization
     */
    final private function setup_fontface()
    {
        if (!$this->env->is_optimization()) {
            return;
        }

        // requestAnimationFrame timed render
        $raf_timed_render = false;
        // get config
        $fontface_config = $this->options->get('fonts.fontface.config', array());
        if (!empty($fontface_config)) {

            // HTTP/2 server push WOFF2 fonts
            $http2_push = ($this->options->bool('fonts.http2_push.enabled') && $this->core->module_loaded('http2'));

            // compress array
            foreach ($fontface_config as $config_index => $font_config) {
                if (isset($font_config['families'])) {
                    foreach ($font_config['families'] as $family_index => $family) {

                            // conver Google CDN urls
                        if (isset($family['src'])) {
                            $fontface_config[$config_index]['families'][$family_index]['src'] = $this->compress_font_sources($family['src']);

                            // add font to HTTP/2 Server Push
                            if ($http2_push) {
                                if (is_array($family['src'])) {
                                    if (isset($family['src']['woff2'])) {
                                        Core::get('http2')->push($family['src']['woff2'], 'font');
                                    }
                                } elseif (substr($family['src'], -6) === '.woff2') {
                                    Core::get('http2')->push($family['src'], 'font');
                                }
                            }
                        }

                        if (isset($family['options']) && is_array($family['options'])) {
                            $fontface_config[$config_index]['families'][$family_index]['options'] = $this->client->config_array_value_index($family['options'], array('fonts'), true);
                        }
                    }
                }
            }

            // font face config
            $fontface_config = $this->client->config_array_key_index($fontface_config, array('fonts'), true);

            // client config
            $client_config = array();
            if (!empty($fontface_config)) {
                $client_config[$this->client->config_index('fonts', 'config')] = $fontface_config;
            }

            $load_position = $this->options->get('fonts.fontface.load_position', 'header');
            if ($load_position === 'footer') {
                // set load position
                $client_config[$this->client->config_index('fonts', 'load_position')] = $this->client->config_index('key', 'footer');
            } elseif ($load_position === 'timed') {

                    // add timed exec module
                $this->client->load_module('timed-exec');

                // set load position
                $client_config[$this->client->config_index('fonts', 'load_position')] = $this->client->config_index('key', 'timing');

                // timing type
                $timing_type = $this->options->get('fonts.fontface.load_timing.type');
                switch ($timing_type) {
                        case "media":

                            // add responsive exec module
                            $this->client->load_module('responsive');
                        break;
                        case "inview":

                            // add inview exec module
                            $this->client->load_module('inview');
                        break;
                    }

                // timing config
                $timing_config = $this->timing_config($this->options->get('fonts.fontface.load_timing.*'));
                if ($timing_config) {

                        // set load timing config
                    $client_config[$this->client->config_index('fonts', 'load_timing')] = $this->client->config_array_key_index($timing_config, array('key'), true);
                }
            } else {
                if ($this->options->bool('fonts.fontface.render_timing.enabled')) {
                        
                        // add timed exec module
                    $this->client->load_module('timed-exec');

                    // timing type
                    $timing_type = $this->options->get('fonts.fontface.render_timing.type');
                    switch ($timing_type) {
                            case "requestAnimationFrame":
                                $raf_timed_render = true;
                            break;
                            case "media":

                                // add responsive exec module
                                $this->client->load_module('responsive');
                            break;
                            case "inview":

                                // add inview exec module
                                $this->client->load_module('inview');
                            break;
                        }

                    // timing config
                    $timing_config = $this->timing_config($this->options->get('fonts.fontface.render_timing.*'));
                    if ($timing_config) {

                            // set render timing config
                        $client_config[$this->client->config_index('fonts', 'render_timing')] = $this->client->config_array_key_index($timing_config, array('key'), true);
                    }
                }
            }

            // requestAnimationFrame render
            if (!$raf_timed_render && $this->options->bool('fonts.fontface.requestAnimationFrame')) {

                    // add timed exec module
                $this->client->load_module('timed-exec');
                    
                $client_config[$this->client->config_index('fonts', 'requestAnimationFrame')] = true;
            }

            // set config
            if (!empty($client_config)) {
                $this->client->set_config('fonts', 'fontface', $client_config);
            }
        }

        // add font config loader module
        $this->client->load_module('fonts-loadconfig', O10N_CORE_VERSION, $this->core->modules('fonts')->dir_path());

        // add fontface module
        $this->client->load_module('fonts-fontface', O10N_CORE_VERSION, $this->core->modules('fonts')->dir_path());
    }

    /**
     * Setup Font Face Observer optimization
     */
    final private function setup_observer()
    {
        if (!$this->env->is_optimization()) {
            return;
        }

        // get config
        $observer_config = $this->options->get('fonts.fontfaceobserver.config', array());
        if (!empty($observer_config)) {

                // compress array
            foreach ($observer_config as $config_index => $font_config) {
                if (isset($font_config['families'])) {
                    foreach ($font_config['families'] as $family_index => $family) {

                            // conver Google CDN urls
                        if (isset($family['src'])) {
                            $observer_config[$config_index]['families'][$family_index]['src'] = $this->compress_font_sources($family['src']);
                        }

                        if (isset($family['options']) && is_array($family['options'])) {
                            $observer_config[$config_index]['families'][$family_index]['options'] = $this->client->config_array_value_index($family['options'], array('fonts'), true);
                        }
                    }
                }
            }

            $observer_config = $this->client->config_array_key_index($observer_config, array('fonts'), true);

            // client config
            $client_config = array();
            if (!empty($observer_config)) {
                $client_config[$this->client->config_index('fonts', 'config')] = $observer_config;
            }

            $load_position = $this->options->get('fonts.fontfaceobserver.load_position', 'header');
            if ($load_position === 'footer') {
                // set load position
                $client_config[$this->client->config_index('fonts', 'load_position')] = $this->client->config_index('key', 'footer');
            } elseif ($load_position === 'timing') {

                    // add timed exec module
                $this->client->load_module('timed-exec');

                // set load position
                $client_config[$this->client->config_index('fonts', 'load_position')] = $this->client->config_index('key', 'timing');

                // timing type
                $timing_type = $this->options->get('fonts.fontfaceobserver.load_timing.type');
                switch ($timing_type) {
                        case "media":
                            
                            // add responsive exec module
                            $this->client->load_module('responsive');
                        break;
                        case "inview":
                            
                            // add inview module
                            $this->client->load_module('inview');
                        break;
                    }

                // timing config
                $timing_config = $this->timing_config($this->options->get('fonts.fontfaceobserver.load_timing.*'));
                if ($timing_config) {

                        // set load timing config
                    $client_config[$this->client->config_index('fonts', 'load_timing')] = $this->client->config_array_key_index($timing_config, array('key'), true);
                }
            }

            $this->client->set_config('fonts', 'observer', $client_config);
        }

        // add font config loader module
        $this->client->load_module('fonts-loadconfig', O10N_CORE_VERSION, $this->core->modules('fonts')->dir_path());

        // add fontface module
        $this->client->load_module('fonts-observer', O10N_CORE_VERSION, $this->core->modules('fonts')->dir_path());
    }

    /**
     * Setup Google Font Loader optimization
     */
    final private function setup_gfl()
    {
        if (!$this->env->is_optimization()) {
            return;
        }
        
        $gfl_config = array();

        $load_position = $this->options->get('fonts.googlefontloader.load_position');
        if ($load_position === 'footer') {
            // set load position
            $gfl_config[$this->client->config_index('fonts', 'load_position')] = $this->client->config_index('key', 'footer');
        } elseif ($load_position === 'timing') {

                // add timed exec module
            $this->client->load_module('timed-exec');

            // set load position
            $gfl_config[$this->client->config_index('fonts', 'load_position')] = $this->client->config_index('key', 'timing');

            // timing type
            $timing_type = $this->options->get('fonts.googlefontloader.load_timing.type');
            switch ($timing_type) {
                case "media":
                    
                    // add responsive exec module
                    $this->client->load_module('responsive');
                break;
                case "inview":
                    
                    // add inview module
                    $this->client->load_module('inview');
                break;
            }

            // timing config
            $timing_config = $this->timing_config($this->options->get('fonts.googlefontloader.load_timing.*'));
            if ($timing_config) {

                    // set load timing config
                $gfl_config[$this->client->config_index('fonts', 'load_timing')] = $this->client->config_array_key_index($timing_config, array('key'), true);
            }
        }

        // add google font loader module
        $this->client->load_module('webfontloader', O10N_CORE_VERSION, $this->core->modules('fonts')->dir_path());

        $this->client->load_module('fonts-gfl', O10N_CORE_VERSION, $this->core->modules('fonts')->dir_path());


        if (!empty($gfl_config)) {
            // set config
            $this->client->set_config('fonts', 'gfl', $gfl_config);
        }

        // add web font config
        $webfontconfig = $this->options->get('fonts.googlefontloader.config');
        if ($webfontconfig) {
            $this->client->after('client', '<script data-o10n>o10n.fonts(' . rtrim(trim(preg_replace(array('|\n+|s','|\s+|s'), array('',' '), $webfontconfig)), ';') . ');</script>');
        }


        // Remove existing web font loader and config
        if ($this->options->bool('fonts.googlefontloader.remove')) {

            // use Javascript optimization filters
            if (defined('O10N_JS_VERSION')) {
                
                // remove GFL script tags / inline textx
                add_filter('o10n_script_src_pre', array( $this, 'remove_gfl_src' ), 10, 1);
                add_filter('o10n_script_text_pre', array( $this, 'remove_gfl_text' ), 10, 1);

                // remove GFL from external scripts
                add_filter('o10n_proxy_js_content', array( $this, 'remove_gfl_text' ), 10, 1);
            } else {

                // use HTML output filter
                add_filter('o10n_html_pre', array( $this, 'remove_gfl_html' ), 10, 1);
            }
        }
    }

    /**
     * Remove linked fonts from HTML
     */
    final public function remove_linked_html($HTML)
    {
        if (!empty($this->font_removal_list)) {
            $search = array();
            $replace = array();

            // stylesheet regex
            $stylesheet_regex = '#(<\!--\[if[^>]+>\s*)?<link([^>]+)>#Usmi';

            if (preg_match_all($stylesheet_regex, $HTML, $out)) {
                foreach ($out[2] as $n => $stylesheet) {
                    foreach ($this->font_removal_list as $remove) {
                        if (stripos($stylesheet, $remove) !== false) {
                            $search[] = $out[0][$n];
                            $replace[] = '';
                        }
                    }
                }
            }

            // remove matched links
            if (!empty($search)) {
                $this->output->add_search_replace($search, $replace);
            }
        }

        
        return $HTML;
    }

    /**
     * Remove linked font stylesheets using CSS Optimization filter
     */
    final public function remove_linked_stylesheet($href)
    {
        if (!empty($this->font_removal_list)) {
            foreach ($this->font_removal_list as $remove) {
                if (stripos($href, $remove) !== false) {
                    return 'delete';
                }
            }
        }

        return $href;
    }

    /**
     * Remove linked font @import from content
     *
     * @param string $CSS CSS to filter @import rules from
     *
     */
    final public function remove_linked_import($CSS)
    {
        if (!empty($this->font_removal_list)) {
            $search = array();
            $replace = array();

            // extract @import links from CSS
            if (preg_match_all('#(?:@import)(?:\\s)(?:url)?(?:(?:(?:\\()(["\'])?(?:[^"\')]+)\\1(?:\\))|(["\'])(?:.+)\\2)(?:[A-Z\\s])*)+(?:;)#Ui', $CSS, $out)) {
                foreach ($out[0] as $n => $fontLink) {
                    foreach ($this->font_removal_list as $remove) {
                        if (stripos($fontLink, $remove) !== false) {
                            $search[] = $out[0][$n];
                            $replace[] = '';
                        }
                    }
                }
            }

            // remove matched links
            if (!empty($search)) {
                $this->output->add_search_replace($search, $replace);
            }
        }

        return $CSS;
    }

    /**
     * Remove Google Font Loader from HTML
     */
    final public function remove_gfl_html($HTML)
    {

        // disable WebFontConfig
        if (strpos($HTML, 'WebFontConfig') !== false) {
            $this->output->add_search_replace('WebFontConfig', '_x_o10n');
        }

        // replace WebFont.load method with IIFE dummy
        if (strpos($HTML, 'WebFont.load') !== false) {
            $this->output->add_search_replace('WebFont.load', '(function() {})');
        }

        // remove Google CDN linked webfont.js scripts
        if (strpos($HTML, '/webfont.js') !== false) {
            $this->output->add_search_replace('|\s*<script[^>]+googleapis\.com/[^>]+/webfont\.js[^>]+>(\s*</script>)?\s*|si', '', true);
        }

        return $HTML;
    }
  
    /**
     * Remove Google Font Loader scripts
     */
    final public function remove_gfl_src($src)
    {
        if (strpos($src, '/webfont.js') !== false && preg_match('/googleapis\.com/.*/webfont\.js/si', $src)) {
            return 'delete';
        }

        return $src;
    }
  
    /**
     * Remove Google Font Loader from inline scripts
     */
    final public function remove_gfl_text($text)
    {
        // disable WebFontConfig
        if (strpos($text, 'WebFontConfig') !== false) {
            $text = str_replace('WebFontConfig', '_x_o10n', $text);
        }

        // replace WebFont.load method with IIFE dummy
        if (strpos($text, 'WebFont.load') !== false) {
            $text = str_replace('WebFont.load', '(function() {})', $text);
        }

        return $text;
    }

    /**
     * Return timing config
     *
     * @param  mixed $src Font sources
     * @return mixed Compressed font sources
     */
    final private function compress_font_sources($src)
    {
        if (is_array($src)) {
            foreach ($src as $font_type => $uri) {

                // Google Font CDN
                if (strpos($uri, 'https://fonts.gstatic.com/') !== false) {
                    $src[$font_type] = 'g:'.substr($uri, 26);
                }
            }
        } else {

            // Google Font CDN
            if (strpos($src, 'https://fonts.gstatic.com/') !== false) {
                $src[$font_type] = 'g:'.substr($src, 26);
            }
        }

        return $src;
    }

    /**
     * Return timing config
     *
     * @param   array   Timing config
     * @return array Client compressed timing config
     */
    final private function timing_config($config)
    {
        if (!$config || !is_array($config) || !isset($config['type'])) {
            return false;
        }


        // init config with type index
        $timing_config = array($this->client->config_index('key', $config['type']));

        // timing config
        switch (strtolower($config['type'])) {
            case "requestanimationframe":
                
                // frame
                $frame = (isset($config['frame']) && is_numeric($config['frame'])) ? $config['frame'] : 1;
                if ($frame > 1) {
                    $timing_config[1] = array();
                    $timing_config[1][$this->client->config_index('key', 'frame')] = $frame;
                }
            break;
            case "inview":

                // selector
                $selector = (isset($config['selector'])) ? trim($config['selector']) : '';
                if ($selector !== '') {
                    $timing_config[1] = array();
                    $timing_config[1][$this->client->config_index('key', 'selector')] = $selector;
                }

                // offset
                $offset = (isset($config['offset']) && is_numeric($config['offset'])) ? $config['offset'] : 0;
                if ($offset > 0) {
                    if (!isset($timing_config[1])) {
                        $timing_config[1] = array();
                    }
                    $timing_config[1][$this->client->config_index('key', 'offset')] = $offset;
                }
            break;
            case "media":

                // media query
                $media = (isset($config['media'])) ? trim($config['media']) : '';
                if ($media !== '') {
                    $timing_config[1] = array();
                    $timing_config[1][$this->client->config_index('key', 'media')] = $media;
                }
            break;
        }

        return $timing_config;
    }
}
