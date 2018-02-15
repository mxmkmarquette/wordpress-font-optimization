<?php
namespace O10n;

/**
 * Concat Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Concat extends Controller implements Controller_Interface
{
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
            'cache'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
    }

    /**
     * Sanitize group filter
     */
    final public function sanitize_filter($concat_filter)
    {
        if (!is_array($concat_filter) || empty($concat_filter)) {
            $concat_filter = false;
        }

        // sanitize groups by key reference
        $sanitized_groups = array();
        foreach ($concat_filter as $filter) {
            if (!isset($filter['match']) || empty($filter['match'])) {
                continue;
            }

            if (isset($filter['group']) && isset($filter['group']['key'])) {
                $sanitized_groups[$filter['group']['key']] = $filter;
            } else {
                $sanitized_groups[] = $filter;
            }
        }

        return $sanitized_groups;
    }

    /**
     * Apply filter
     */
    final public function apply_filter(&$concat_group, &$concat_group_settings, $tag, $concat_filter)
    {
        if (!is_array($concat_filter)) {
            throw new Exception('Concat group filter not array.', 'core');
        }

        $filter_set = false; // group set flag
        
        // match group filter list
        foreach ($concat_filter as $key => $filter) {

            // verify filter config
            if (!is_array($filter) || empty($filter) || (!isset($filter['match']) && !isset($filter['match_regex']))) {
                continue 1;
            }

            // exclude rule
            $exclude_filter = (isset($filter['exclude']) && $filter['exclude']);

            // string based match
            if (isset($filter['match']) && !empty($filter['match'])) {
                foreach ($filter['match'] as $match_string) {
                    $exclude = false;
                    $regex = false;

                    // filter config
                    if (is_array($match_string)) {
                        $exclude = (isset($match_string['exclude'])) ? $match_string['exclude'] : false;
                        $regex = (isset($match_string['regex'])) ? $match_string['regex'] : false;
                        $match_string = $match_string['string'];
                    }

                    // group set, just apply exclude filters
                    if ($filter_set && !$exclude && !$exclude_filter) {
                        continue 1;
                    }

                    if ($regex) {
                        $match = false;
                        try {
                            if (@preg_match($match_string, $tag)) {

                                // exclude filter
                                if ($exclude || $exclude_filter) {
                                    $concat_group = false;

                                    return;
                                }

                                $match = true;
                            }
                        } catch (\Exception $err) {
                            $match = false;
                        }

                        if ($match) {

                            // match, assign to group
                            $concat_group = md5(json_encode($filter));
                            if (!isset($concat_group_settings[$concat_group])) {
                                $concat_group_settings[$concat_group] = array();
                            }
                            $concat_group_settings[$concat_group] = array_merge($filter, $concat_group_settings[$concat_group]);
                            
                            $filter_set = true;
                        }
                    } else {
                        if (strpos($tag, $match_string) !== false) {

                            // exclude filter
                            if ($exclude || $exclude_filter) {
                                $concat_group = false;

                                return;
                            }

                            // match, assign to group
                            $concat_group = md5(json_encode($filter));
                            if (!isset($concat_group_settings[$concat_group])) {
                                $concat_group_settings[$concat_group] = array();
                            }
                            $concat_group_settings[$concat_group] = array_merge($filter, $concat_group_settings[$concat_group]);

                            $filter_set = true;
                        }
                    }
                }
            }
        }
    }

    /**
     * Return concat hash path for async list
     *
     * @param  string $hash Hash key for concat stylesheet
     * @return string Hash path for async list.
     */
    final public function async_hash_path($type, $hash)
    {
        // get index id
        $index_id = $this->cache->index_id($type . ':concat', $hash);

        if (!$index_id) {
            throw new Exception('Failed to retrieve concat hash index ID.', 'text');
        }
        if (is_array($index_id)) {
            $suffix = $index_id[1];
            $index_id = $index_id[0];
        } else {
            $suffix = false;
        }

        // return hash path
        return str_replace('/', '|', $this->cache->index_path($index_id)) . $index_id . (($suffix) ? ':' . $suffix : '');
    }
}
