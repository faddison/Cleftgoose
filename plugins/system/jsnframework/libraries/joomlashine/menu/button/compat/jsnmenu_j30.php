<?php
/**
 * @version    $Id$
 * @package    JSN_Framework
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import Joomla library
jimport('joomla.html.toolbar.button');

/**
 * Class for rendering button that supports dropdown menu to put in Joomla toolbar.
 *
 * @package  JSN_Framework
 * @since    1.0.0
 */
class JToolbarButtonJSNMenu extends JToolbarButton
{
	/**
	 * Variable containing menu items.
	 *
	 * @var  array
	 */
	protected static $items;

	/**
	 * Method to add menu item.
	 *
	 * @param   string  $id      An unique string to identify this sub-menu item.
	 * @param   string  $name    Name of sub-menu item.
	 * @param   string  $link    Link of sub-menu item.
	 * @param   bool    $active  True if the item is active, false otherwise.
	 * @param   string  $icon    Icon of sub-menu item.
	 * @param   string  $parent  Dot separated parent menu item id, e.g. root.2nd_level.3rd_level
	 *
	 * @return  void
	 */
	public static function addEntry($id, $name = '', $link = 'javascript:void(0)', $active = false, $icon = '', $parent = 'jsnmenu')
	{
		// Parse parent menu item id
		$parents	= explode('.', $parent);
		$parent		= & self::$items;

		foreach ($parents AS $pid)
		{
			// Initialize parent entry
			if ( ! isset($parent[$pid]))
			{
				$parent[$pid] = array('submenu' => array());
			}

			$parent = & $parent[$pid]['submenu'];
		}

		// Add entry for menu item
		$parent[$id] = array(
			'name'		=> $name,
			'link'		=> $link,
			'active'	=> $active,
			'icon'		=> $icon
		);
	}

	/**
	 * Method to fetch to sub-menu button id.
	 *
	 * @return  string
	 */
	public function fetchId()
	{
		// Get button parameters
		$params = func_get_args();

		return "jsn-menu-item-{$params[1]}";
	}

	/**
	 * Method to fetch to HTML markup for rendering sub-menu button.
	 *
	 * @return  string
	 */
	public function fetchButton()
	{
		// Get button parameters:
		//
		// 0: Always 'jsnmenu' as the first index is button type.
		// 1: Id of menu item to search for its children.
		// 2: Menu item title.
		// 3: Menu item link.
		// 4: Menu item active state.
		// 5: CSS icon class, e.g. 'jsn-icon32 jsn-icon-menu', 'jsn-icon24 jsn-icon-file', etc.
		// 6: Id of parent menu item, if the value is 'toolbar' then this is a button in Joomla's toolbar.
		$params = func_get_args();

		// Prepare data
		$icon = empty($params[5]) ? '' : '<span class="' . $params[5] . '"></span> ';
		$text = JText::_($params[2]);

		// Generate markup tag for drop-down menu
		$html[] = '
<ul class="jsn-menu">
	<li class="menu-name">
		<button class="btn btn-small">' . $icon . $text . '</button>';

		// Get markup tag for sub-menu
		if (@is_array(self::$items[$params[1]]) AND @is_array(self::$items[$params[1]]['submenu']))
		{
			$html[] = $this->renderSubMenu(self::$items[$params[1]]['submenu'], true);
		}

		// Finalize markup tag for drop-down menu
		$html[] = '
	</li>
</ul>';

		// Load assets
		JSNHtmlAsset::loadScript('jsn/menu');

		return implode($html);
	}

	/**
	 * Method to render list of root menu item.
	 *
	 * @param   string  $parent  Dot separated parent menu item id, e.g. root.2nd_level.3rd_level, to get menu item from
	 *
	 * @return  string
	 */
	public function renderMenu($parent = 'jsnmenu')
	{
		// Preset markup tag for root menu
		$html = '';

		// Get sub-menu items of specified parent
		$parents	= explode('.', $parent);
		$parentRef	= & self::$items;

		foreach ($parents AS $pid)
		{
			// Initialize parent entry
			if ( ! isset($parentRef[$pid]))
			{
				throw new Exception(JText::_('JSN_EXTFW_MENU_INVALID_PARENT_ID'));
			}
			else
			{
				$parentRef = & $parentRef[$pid]['submenu'];
			}
		}

		// Render all sub-menu items of specified parent as root menu items
		$html = $this->renderSubMenu($parentRef);

		return $html;
	}

	/**
	 * Method to render sub-menu items.
	 *
	 * @param   array  $items       Array of sub-menu items.
	 * @param   bool   $nestedMenu  Is this a sub-menu of another sub-menu item.
	 *
	 * @return  string
	 */
	protected function renderSubMenu($items = array(), $nestedMenu = false)
	{
		// Generate markup tag for sub-menu
		$html[] = '
		<ul ' . ($nestedMenu ? 'class="jsn-submenu"' : 'id="submenu" class="jsn-menu clearafter"') . '>';

		$isFirst = true;

		foreach ($items AS $id => $data)
		{
			// Is this a separator
			if (empty($data['name']) AND empty($data['link']))
			{
				$html[] = '
			<li class="separator"></li>';

				// Continue immediately as separator does not need further processing
				continue;
			}

			// Prepare data for LI tag
			if ($nestedMenu)
			{
				$class = array();

				empty($data['icon'])		OR $class[] = 'primary';
				! $isFirst					OR $class[] = 'first';
				! $data['active']			OR $class[] = 'active';
				! isset($data['submenu'])	OR $class[] = 'parent';

				$class = count($class) ? ' class="' . implode(' ', $class) . '"' : '';

				// Prepare data for A tag
				$attrs = array(' id="' . $id . '"');

				empty($data['link']) OR $attrs[] = 'href="' . JRoute::_($data['link']) . '"';

				$attrs = implode(' ', $attrs);

				$icon = empty($data['icon']) ? '' : '<span class="' . $data['icon'] . '"></span> ';
				$text = JText::_($data['name']);

				$html[] = '
			<li'. $class . '>
				<a' . $attrs . '>' . $icon . $text . '</a>';
			}
			else
			{
				$listClass = isset($data['submenu']) ? ' class="parent"' : '';
				$linkClass = $data['active'] ? ' class="active"' : '';

				$link = $data['link'] ? $data['link'] : 'javascript:void(0)';

				$html[] = '
			<li' . $listClass . '>
				<a' . $linkClass . ' href="' . $link . '">' . JText::_($data['name']) . '</a>';
			}

			// Get markup tag for sub-menu
			if (@is_array($data['submenu']))
			{
				$html[] = $this->renderSubMenu($data['submenu'], true);
			}

			$html[] = '
			</li>';

			$isFirst = false;
		}

		// Finalize markup tag for sub-menu
		$html[] = '
		</ul>';

		return implode($html);
	}
}
