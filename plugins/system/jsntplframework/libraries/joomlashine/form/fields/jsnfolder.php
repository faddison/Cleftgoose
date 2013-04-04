<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  JSNTPL
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Load base class
require_once JSN_PATH_TPLFRAMEWORK . '/libraries/joomlashine/form/field.php';
require_once JSN_PATH_TPLFRAMEWORK . '/libraries/joomlashine/form/helper.php';

/**
 * Custom field to output about section for the framework
 * 
 * @package     JSNTPL
 * @subpackage  Form
 * @since       1.0.0
 */
class JFormFieldJSNFolder extends JSNTPLFormField
{
	public $type = 'JSNFolder';

	/**
	 * Return HTML markup for the field
	 * 
	 * @return  string
	 */
	public function getInput ()
	{
		$disabled = isset($this->element['disabled']) && $this->element['disabled'] == 'true' ? 'disabled' : '';

		$html  = '<div class="input-append ' . $disabled . '">';
		$html .= JSNTplFormHelper::input($this->name, $this->value, array('id' => $this->id, 'disabled' => $disabled == 'disabled'));
		$html .= '	<a href="javascript:void(0)" class="btn ' . $disabled . '">' . JText::_($this->element['verifyLabel']) . '</a>';
		$html .= '</div>';

		return $html;
	}
}
