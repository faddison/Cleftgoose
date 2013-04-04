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

$doc = JFactory::getDocument();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo 'xml:lang="' . $doc->getLanguage() . '" lang="' . $doc->getLanguage() . '" dir="' . $doc->getDirection() . '"'; ?>>
<head>
<?php

/** Add Scripts and StyleSheets to header of page **/
$header = array();
$header[] = $jsnrender->getHeader();
echo implode(PHP_EOL, $header);
?>
<style type="text/css">
	body{
		background:#FFF;
	}
</style>
</head>
<?php
	$body = $jsnrender->getBody();
?>
<body <?php echo $body->attr;?>>
	<div id="jsn-page-container" >
		<?php echo $body->html;?>
	</div>
</body>
</html>
