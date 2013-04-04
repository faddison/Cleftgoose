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
 * Template update widget
 * 
 * @package     JSNTPLFramework
 * @subpackage  Template
 * @since       1.0.0
 */
class JSNTplWidgetUpdate extends JSNTplWidgetBase
{
	private $_templateVersionData;

	/**
	 * Authentication action before install sample data
	 * 
	 * @return  void
	 */
	public function confirmAction ()
	{
		$target = $this->request->getString('target');
		if ($target == 'framework') {
			$this->render('framework_confirm', array(
				'manifest' => JSNTplHelper::getManifestCache('jsntplframework')
			));

			return;
		}

		// Process posted back data that sent from client
		if ($this->request->getMethod() == 'POST')
		{
			// Checking customer information
			$input = JFactory::getApplication()->input;
			$username = $input->getString('username', '');
			$password = $input->getString('password', '');

			// Try retrieve ordered editions to check customer information
			JSNTplApiLightcart::getOrderedEditions($this->template['id'], $username, $password);
			return;
		}

		// Retrieve version data
		$versionData = JSNTplHelper::getVersionData();

		// Find template information by identify name
		foreach ($versionData['items'] as $item)
		{
			if ($item['identified_name'] == $this->template['id']) {
				if (count($item['editions']) == 1) {
					$template = $item['editions'][0];

					// Render login view
					$this->render('confirm', array(
						'template' => $this->template,
						'authenticate' => $template['authentication']
					));
				}
				else {
					foreach ($item['editions'] as $template) {
						$edition = trim($template['edition']);
						if (strpos($template['edition'], 'PRO ') === 0) {
							$edition = substr($template['edition'], 4);
						}

						if ($this->template['edition'] == $edition) {
							// Render login view
							$this->render('confirm', array(
								'template' => $this->template,
								'authenticate' => $template['authentication']
							));

							break;
						}
					}
				}

				break;
			}
		}
	}

	/**
	 * Render UI for install update screen
	 * 
	 * @return  void
	 */
	public function installAction ()
	{
		$target = $this->request->getString('target');
		if ($target == 'framework') {
			$this->render('framework_install', array(
				'manifest' => JSNTplHelper::getManifestCache('jsntplframework')
			));

			return;
		}

		$this->render('install', array('template' => $this->template));
	}

	/**
	 * Download update package for template
	 * 
	 * @return  void
	 */
	public function downloadAction ()
	{
		// Process posted back data that sent from client
		if ($this->request->getMethod() == 'POST')
		{
			// Checking customer information
			$input = JFactory::getApplication()->input;
			$username = $input->getString('username', '');
			$password = $input->getString('password', '');

			// Load template xml file
			$edition = strtolower(trim($this->template['edition']));

			if ($edition != 'free') {
				$edition = 'pro ' . $edition;
			}

			if (!JSNTplHelper::isDisabledFunction('set_time_limit')) {
				set_time_limit(0);
			}

			// Download package file
			JSNTplApiLightcart::downloadPackage($this->template['id'], $edition, $username, $password);
		}
	}

	public function downloadFrameworkAction()
	{
		if (!JSNTplHelper::isDisabledFunction('set_time_limit')) {
			set_time_limit(0);
		}

		// Download package file
		JSNTplApiLightcart::downloadPackage('tpl_framework');
	}

	public function installFrameworkAction ()
	{
		$packageFile = JPATH_ROOT . '/tmp/jsn-tpl_framework.zip';

		// Checking downloaded template package
		if (!is_file($packageFile)) {
			throw new Exception(JText::_('JSN_TPLFW_ERROR_DOWNLOAD_PACKAGE_FILE_NOT_FOUND'));
		}

		// Load install library
		jimport('joomla.installer.helper');

		// Turn off debug mode to catch install error
		$conf = JFactory::getConfig();
		$conf->set('debug', 0);
		
		$unpackedInfo	= JInstallerHelper::unpack($packageFile);
		$installer		= new JInstaller();
		$installer->setUpgrade(true);
		$installResult	= $installer->install($unpackedInfo['dir']);
		
		// Clean up temporary data
		JInstallerHelper::cleanupInstall($packageFile, $unpackedInfo['dir']);

		// Clean up compressed files
		$this->_cleanCache();

		// Send error if install is failure
		if (class_exists('JError')) {
			$error = JError::getError();
			if (!empty($error))
				throw $error;
		}
	}

	/**
	 * Backup all modified files
	 * 
	 * @return  void
	 */
	public function backupAction ()
	{
		// Find all modified files
		$modifiedFiles = JSNTplHelper::getModifiedFiles($this->template['name']);

		// Create temporary folder for store backup files
		$config		= JFactory::getConfig();
		$tmpPath	= $config->get('tmp_path');
		$backupPath = $tmpPath . "/{$this->template['name']}_backup";
		$backupUrl  = JURI::root(true) . '/tmp/' . $this->template['name'] . '_backup.zip';

		$templatePath = JPATH_ROOT . "/templates/{$this->template['name']}";

		if (!is_dir($backupPath)) {
			mkdir($backupPath);
		}

		$files = array();

		// Copy all modified files to backup folder
		foreach ($modifiedFiles['edit'] as $file) {
			if (strpos($file, '/') === false && strpos($file, '\\') === false) {
				$path = $backupPath;
			}
			else {
				$filePath = dirname($file);
				$path = "{$backupPath}/{$filePath}";
			}

			JSNTplHelper::makePath($path);
			copy("{$templatePath}/{$file}", "{$backupPath}/{$file}");

			$files[] = array(
				'name' => "{$file}",
				'data' => file_get_contents("{$backupPath}/{$file}")
			);
		}

		$archiver = new JSNTplArchiveZip();
		$archiver->create($backupPath . '.zip', $files);

		$this->setResponse($backupUrl);
	}

	/**
	 * Checking template version for auto update
	 * 
	 * @return  void
	 */
	public function checkUpdateAction ()
	{
		$templateManifest	= JSNTplHelper::getManifestCache($this->template['name']);
		$frameworkManifest	= JSNTplHelper::getManifestCache('jsntplframework');

		$response = array();

		try {
			$templateInfo		= JSNTplApiLightcart::getProductDetails('cat_template', $this->template['id']);
			$response['template'] = array(
				'currentVersion'	=> $templateManifest->version,
				'newVersion'		=> $templateInfo->version,
				'hasUpdate'			=> version_compare($templateInfo->version, $templateManifest->version, '>')
			);
		}
		catch (Exception $ex) {
			$response['template'] = array(
				'currentVersion'	=> $templateManifest->version,
				'newVersion'		=> $templateManifest->version,
				'hasUpdate'			=> false
			);
		}

		try {
			$frameworkInfo		= JSNTplApiLightcart::getProductDetails('cat_template', JSN_TPLFRAMEWORK_ID);
			$response['framework'] = array(
				'currentVersion'	=> $frameworkManifest->version,
				'newVersion'		=> $frameworkInfo->version,
				'hasUpdate'			=> version_compare($frameworkInfo->version, $frameworkManifest->version, '>')
			);
		}
		catch (Exception $ex) {
			$response['framework'] = array(
				'currentVersion'	=> $frameworkManifest->version,
				'newVersion'		=> $frameworkManifest->version,
				'hasUpdate'			=> false
			);
		}

		$this->setResponse($response);
	}

	/**
	 * Start process to install template update
	 * 
	 * @return  void
	 */
	public function installPackageAction ()
	{
		$packageFile = JPATH_ROOT . '/tmp/jsn-' . $this->template['id'] . '.zip';

		// Checking downloaded template package
		if (!is_file($packageFile)) {
			throw new Exception(JText::_('JSN_TPLFW_ERROR_DOWNLOAD_PACKAGE_FILE_NOT_FOUND'));
		}

		// Load install library
		jimport('joomla.installer.helper');

		// Turn off debug mode to catch install error
		$conf = JFactory::getConfig();
		$conf->set('debug', 0);
		
		$unpackedInfo	= JInstallerHelper::unpack($packageFile);
		$installer		= new JInstaller();
		$installer->setUpgrade(true);
		$installResult	= $installer->install($unpackedInfo['dir']);
		
		// Clean up temporary data
		JInstallerHelper::cleanupInstall($packageFile, $unpackedInfo['dir']);

		// Clean up compressed files
		$this->_cleanCache();

		// Send error if install is failure
		if (class_exists('JError')) {
			$error = JError::getError();
			if (!empty($error))
				throw $error;
		}
	}

	private function _cleanCache ()
	{
		// Clean up compressed files
		$compressDir = JPATH_ROOT . '/tmp/' . $this->template['name'];
		
		if (is_dir($compressDir)) {
			jimport('joomla.filesystem.folder');
			JFolder::delete($compressDir);
		}
	}
}
