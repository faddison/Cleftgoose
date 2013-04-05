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


class modContinuousRssScrollingHelper
{
	function loadScripts(&$params)
	{
		$doc = &JFactory::getDocument();
		$doc->addScript(JURI::Root(true).'/modules/mod_continuous_rss_scrolling/mod_continuous_rss_scrolling.js');
	}
	
	public function getFeed($args)
	{
		
		@$url = $args['crs_rss_url'];
		
		if(@$url == "")
		{
			@$url = "http://www.gopiplus.com/work/feed";
		}
		
		@$items	= array();
		@$xml = "";
		@$cnt=0;
		@$f = fopen( $url, 'r' );
		while( $data = fread( $f, 4096 ) ) { $xml .= $data; }
		fclose( $f );
		preg_match_all( "/\<item\>(.*?)\<\/item\>/s", $xml, $itemblocks );
		@$i=0;
		if ( ! empty($itemblocks) ) 
		{
			foreach( $itemblocks[1] as $block )
			{
				preg_match_all( "/\<title\>(.*?)\<\/title\>/",  $block, $title );
				preg_match_all( "/\<link\>(.*?)\<\/link\>/", $block, $link );
				$crs_post_title = $title[1][0];
				$crs_post_title = mysql_real_escape_string(trim($crs_post_title));
				$crs_post_link = $link[1][0];
				$crs_post_link = mysql_real_escape_string(trim($crs_post_link));
				$items[$i]->title	= $crs_post_title;
				$items[$i]->link	= $crs_post_link;
				$i++;			
			}	
		}
		return $items;
	}
}
?>