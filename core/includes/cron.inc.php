<?php
namespace O10n;

/**
 * Cron related global functions
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     Optimization.Team <info@optimization.team>
 */

// Cache prune cron
function cron_prune_cache()
{
    Core::get('cache')->prune();
}

// Cache expire cron
function cron_prune_expired_cache()
{
    Core::get('cache')->prune_expired();
}
