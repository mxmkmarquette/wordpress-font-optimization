<?php
namespace O10n;

/**
 * CSS settings admin template
 *
 * @package    optimization
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH') || !defined('O10N_ADMIN')) {
    exit;
}


// print form header
$this->form_start(__('Web Font Settings', 'o10n'), 'fonts');

// get css config
$css_config = $this->options->get('fonts.*');

// Convert input to JSON
require_once O10N_CORE_PATH . 'lib/Dot.php';
$dot = new Dot;
$dot->set($css_config);
$profile_json = $dot->toJSON();

?>


<table class="form-table">
    <tr valign="top">
        <th scope="row">Config Profile</th>
        <td>
            <p style="font-size:16px;">The configuration of this plugin is based on a JSON schema. The following JSON contains the full configuration of this plugin. You can backup and restore the configuration using the editor.</p>
            <br />
            <div id="fonts"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'o10n'); ?></div></div>
            <input type="hidden" class="json" name="o10n[fonts]" data-json-type="json" data-json-editor-height="auto" data-json-editor-init="1" value="<?php print esc_attr($profile_json); ?>" />
        </td>
    </tr>
</table>

<hr />
<?php
    submit_button(__('Save'), 'primary large', 'is_submit', false);

// print form header
$this->form_end();
