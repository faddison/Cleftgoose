<?php
/**
 * Continuous rss scrolling
 *
 * @package Continuous rss scrolling
 * @subpackage Continuous rss scrolling
 * @version   3.0 August, 2011
 * @author    Gopi http://www.gopiplus.com
 * @copyright Copyright (C) 2010 - 2011 www.gopiplus.com, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

// no direct access
defined('_JEXEC') or die;

// include the helper file
require_once(dirname(__FILE__).DS.'helper.php');

$args['crs_rss_url'] = $params->get('crs_rss_url');
$args['crs_record_height'] = $params->get('crs_record_height');
$args['crs_display_count'] = $params->get('crs_display_count');
$args['crs_display_width'] = $params->get('crs_display_width');
$crs_cache = $params->get('crs_cache');

$cache = & JFactory::getCache();

if ($crs_cache) 
{
  $items = $cache->call(array('modContinuousRssScrollingHelper','getFeed'),$args);
}
else
{
  $items = modContinuousRssScrollingHelper::getFeed($args);
}
 
 // load javascript 
modContinuousRssScrollingHelper::loadScripts($params);
 
 // include the template for display
require(JModuleHelper::getLayoutPath('mod_continuous_rss_scrolling'));
?>