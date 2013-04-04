<?php

/**
 * @version     $Id: view.html.php 19013 2012-11-28 04:48:47Z thailv $
 * @package     JSNUniform
 * @subpackage  Submission
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */
defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.view');
jimport('joomla.application.helper');

/**
 * View class for a list of Submission.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_uniform
 * @since       1.5
 */
class JSNUniformViewSubmission extends JSNBaseView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$this->_item = $this->get('Item');
		$this->_infoForm = $this->get('InfoForm');
		$dataContentForm = $this->get('FormPages');
		$this->nextAndPreviousForm = $this->get('NextAndPreviousForm');
		$this->_formPages = $dataContentForm;
		$this->_dataSubmission = $this->get('DataSubmission');
		$this->_dataFields = $this->get('DataFields');

		// Display the template
		parent::display($tpl);
		$this->addAssets();
	}

	/**
	 * Add the libraries css and javascript
	 *
	 * @return void
	 *
	 * @since	1.6
	 */
	protected function addAssets()
	{
		JSNHtmlAsset::addStyle(JSN_UNIFORM_ASSETS_URI . '/css/form.css');
		JSNHtmlAsset::addStyle(JSN_URL_ASSETS . '/3rd-party/jquery-tipsy/tipsy.css');
		if (JSNVersion::isJoomlaCompatible('2.5'))
		{
			JSNHtmlAsset::addScript(JSN_URL_ASSETS . '/3rd-party/jquery/jquery-1.7.1.min.js');
			JSNHtmlAsset::addScriptLibrary('jquery.ui', '3rd-party/jquery-ui/js/jquery-ui-1.8.16.custom.min', array('jquery'));
		}
		echo JSNHtmlAsset::loadScript('uniform/submission', array('nextAndPreviousForm' => $this->nextAndPreviousForm), true);
	}

}
