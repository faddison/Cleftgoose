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

// Import library
require_once(dirname(__FILE__) . '/button/jsnmenu.php');

/**
 * Helper class for creating and rendering menu.
 *
 * @package  JSN_Framework
 * @since    1.0.0
 */
abstract class JSNMenuHelper
{
	/**
	 * Flag to indicate whether the sub-menu renderer class is registered.
	 *
	 * @var  boolean
	 */
	protected static $registered;

	/**
	 * Method to add a menu item to submenu.
	 *
	 * !!!NOTE!!! Set 'toolbar' value for $parent parameter will add a menu button to Joomla toolbar.
	 *
	 * @param   string  $id      An unique string to identify this sub-menu item.
	 * @param   string  $name    Name of sub-menu item.
	 * @param   string  $link    Link of sub-menu item.
	 * @param   bool    $active  True if the item is active, false otherwise.
	 * @param   string  $parent  Dot separated parent menu item id, e.g. root.2nd_level.3rd_level
	 * @param   string  $icon    Icon of sub-menu item.
	 *
	 * @return  void
	 */
	public static function addEntry($id, $name = '', $link = 'javascript:void(0)', $active = false, $icon = '', $parent = 'jsnmenu')
	{
		if ( ! $parent OR $parent == 'toolbar')
		{
			// Register the menu renderer class
			self::register();

			// Add the menu item with type 'jsnmenu'
			$menu = JToolBar::getInstance('toolbar');
			$menu->appendButton('jsnmenu', $id, $name, $link, $active, $icon, $parent);
		}
		else
		{
			// Add the menu item as child of another menu item
			JSNVersion::isJoomlaCompatible('2.5')
				? JButtonJSNMenu::addEntry($id, $name, $link, $active, $icon, $parent)
				: JToolbarButtonJSNMenu::addEntry($id, $name, $link, $active, $icon, $parent);
		}
	}

	/**
	 * Method to add a separator to submenu.
	 *
	 * @param   string  $parent  Dot separated parent menu item id, e.g. root.2nd_level.3rd_level
	 *
	 * @return  void
	 */
	public static function addSeparator($parent = 'jsnmenu')
	{
		static $index;

		// Generate id for separator
		$index = isset($index) ? $index + 1 : 1;

		// Let's add the separator as a normal menu item
		self::addEntry("separator-{$index}", '', '', false, '', $parent);
	}

	/**
	 * Method to render list of root menu item.
	 *
	 * @param   string  $parent  Dot separated parent menu item id, e.g. root.2nd_level.3rd_level, to get menu item from
	 *
	 * @return  void
	 */
	public static function render($parent = 'jsnmenu')
	{
		// Instantiate a JSN Menu object
		$renderer = JSNVersion::isJoomlaCompatible('2.5') ? new JButtonJSNMenu : new JToolbarButtonJSNMenu;

		// Generate markup tag for sub-menu
		try
		{
			$html = $renderer->renderMenu($parent);
		}
		catch (Exception $e)
		{
			$html = '';
		}

		// Generate markup tag for wrapper
		if ($parent != 'toolbar')
		{
			$html = '<div id="submenu-box"><div class="m">' . $html . '<div class="clr"></div></div></div>';
		}

		// Load assets
		JSNHtmlAsset::loadScript('jsn/menu');

		// Set the HTML as content for 'submenu' module position
		if (JSNVersion::isJoomlaCompatible('2.5'))
		{
			JFactory::getDocument()->setBuffer(
				$html,
				array(
					'type'	=> 'modules',
					'name'	=> 'submenu'
				)
			);
		}
		elseif (JSNVersion::isJoomlaCompatible('3.'))
		{
			echo $html;
		}
	}

	/**
	 * Method to reigster sub-menu renderer class.
	 *
	 * @return  void
	 */
	protected static function register()
	{
		if ( ! isset(self::$registered) OR ! self::$registered)
		{
			// Register path to look for menu toolbar button renderer class
			$bar = JToolBar::getInstance('toolbar');
			$bar->addButtonPath(dirname(__FILE__) . '/button');

			// State that the class path is registered
			self::$registered = true;
		}
	}
}
