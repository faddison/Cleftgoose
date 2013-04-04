<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  JSNTPLFramework
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Sample data installation
 *
 * @package     JSNTPLFramework
 * @subpackage  Template
 * @since       1.0.0
 */
class JSNTplWidgetIntegrity extends JSNTplWidgetBase
{
	/**
	 * Check files modification state based on checksum.
	 * Send list of modified files to client
	 *
	 * @return  void
	 */
	public function checkAction ()
	{
		$modifiedFiles = JSNTplHelper::getModifiedFiles($this->template['name']);
		$hasModification = !empty($modifiedFiles['add']) || !empty($modifiedFiles['delete']) || !empty($modifiedFiles['edit']);

		$this->setResponse(array(
			'hasModification' => !empty($modifiedFiles['add']) || !empty($modifiedFiles['delete']) || !empty($modifiedFiles['edit']),
			'files' => $modifiedFiles
		));
	}

	/**
	 * Create a backup of modified files then force user to download it.
	 *
	 * @return  void
	 */
	public function downloadAction ()
	{
		// Instantiate update widget
		$widget = new JSNTplWidgetUpdate;

		// Create backup of modified files
		$widget->backupAction();

		// Get link to download backup file
		$link = $widget->getResponse();

		// Force user to download backup file
		header('Content-Length: ' . filesize(str_replace(JUri::root(true), JPATH_ROOT, $link)));
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: no-cache, must-revalidate, max-age=60');
		header('Expires: Sat, 01 Jan 2000 12:00:00 GMT');
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $link);
		exit;
	}
}
