<?php
namespace O10n;

/**
 * Intro admin template
 *
 * @package    optimization
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */

if (!defined('ABSPATH')) {
    exit;
}

$module_name = 'WordPress Performance Optimization';
$module_version = '0.0.1';

?>
<div class="wrap">

	<div class="metabox-prefs">
		<div class="wrap about-wrap" style="position:relative;">
			<div style="float:right;">
			</div>
			<h1><?php print $module_name; ?> <?php print $module_version; ?></h1>

			<p class="about-text" style="min-height:inherit;">Thank you for using the <?php print $module_name; ?> plugin by <a href="https://optimization.team/" target="_blank" rel="noopener" style="color:black;text-decoration:none;">Optimization.Team</a></p>
			
			<p class="about-text" style="min-height:inherit;">This plugin is a toolkit for advanced CSS code and delivery optimization for WordPress. <!--The plugin can be used stand alone or as a module for the <a href="#">Performance Optimization plugin</a>.--></p>

			<p class="about-text info_yellow" style="min-height:inherit;"><strong>Warning:</strong> This plugin is intended for optimization professionals and advanced WordPress users.</p>

			<p class="about-text" style="min-height:inherit;">Getting started? Read <a href="https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery" target="_blank">this article</a> about CSS delivery optimization and <a href="https://developers.google.com/web/fundamentals/performance/critical-rendering-path/">this article</a> about Critical Rendering Path (above-the-fold) optimization by Google.</p>

			<p class="about-text" style="min-height:inherit;">If you are happy with the plugin, please consider to <a href="https://wordpress.org/support/plugin/css-optimization/reviews/#new-post" target="_blank" rel="noopener">write a review</a> and <span class="star" style="display:inline-block;vertical-align:middle;"><a class="github-button" data-manual="1" data-size="large" href="https://github.com/o10n-x/wordpress-css-optimization" data-icon="octicon-star" data-show-count="true" aria-label="Star o10n-x/wordpress-css-optimization on GitHub">Star</a></span> on Github.</p>
			</div>

		</div>
	</div>

</div>