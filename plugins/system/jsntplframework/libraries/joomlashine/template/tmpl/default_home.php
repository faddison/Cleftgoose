<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  TPLFramework
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<div class="span6">
	<h2 class="jsn-section-header"><?php echo JText::_('JSN_TPLFW_GETTING_START_TITLE') ?></h2>

	<h3><?php echo JText::_('JSN_TPLFW_GETTING_START_SAMPLE_DATA') ?></h3>
	<?php echo JText::_('JSN_TPLFW_GETTING_START_SAMPLE_DATA_DESC') ?>
	<ul>
		<li>
			<a href="javascript:void(0)" id="install-sample-data" class="btn btn-small"><b><?php echo JText::_('JSN_TPLFW_GETTING_START_INSTALL_SAMPLE_DATA') ?></b></a>
			<p><?php echo JText::_('JSN_TPLFW_GETTING_START_INSTALL_SAMPLE_DATA_DESC') ?></p>
		</li>
		<li>
			<a href="javascript:void(0)" id="get-quickstart-package" class="btn btn-small"><b><?php echo JText::_('JSN_TPLFW_GETTING_START_GET_PACKAGE') ?></b></a>
			<p><?php echo JText::_('JSN_TPLFW_GETTING_START_GET_PACKAGE_DESC') ?></p>
		</li>
	</ul>

	<h3><?php echo JText::_('JSN_TPLFW_GETTING_START_CHECK_DOCUMENT') ?></h3>
	<?php echo JText::_('JSN_TPLFW_GETTING_START_CHECK_DOCUMENT_DESC') ?>
	<ul>
		<li>
			<a href="http://www.joomlashine.com/docs/joomla-templates/template-configuration-videos.html" target="_blank" id="watch-document-videos" class="btn btn-small">
				<b><?php echo JText::_('JSN_TPLFW_GETTING_START_DOCUMENT_WATCH_VIDEOS') ?></b>
			</a>
			<p><?php echo JText::_('JSN_TPLFW_GETTING_START_DOCUMENT_WATCH_VIDEOS_DESC') ?></p>
		</li>
		<li>
			<a href="http://www.joomlashine.com/joomla-templates/<?php echo $templateName ?>-docs.zip" target="_blank" id="read-documentation" class="btn btn-small">
				<b><?php echo JText::_('JSN_TPLFW_GETTING_START_DOCUMENT_READ') ?></b>
			</a>
			<p><?php echo JText::_('JSN_TPLFW_GETTING_START_DOCUMENT_READ_DESC') ?></p>
		</li>
	</ul>
</div>

<div class="span6">
	<div class="jsn-page-about">
		<div class="jsn-product-about jsn-pane jsn-bgpattern pattern-sidebar">
			<h2 class="jsn-section-header">
				<a href="<?php echo $templateLink ?>" target="_blank">
					<?php echo JText::_($this->data->template) ?> <?php echo $edition ?>
				</a>
				<?php if ($nextEdition == 'STANDARD'): ?>
				<a class="btn pull-right jsn-upgrade-link" href="javascript:void()">
					<span class="label label-important">PRO</span><?php echo JText::_('JSN_TPLFW_UPGRADE') ?>
				</a>
				<?php elseif ($nextEdition == 'UNLIMITED'): ?>
				<a class="btn pull-right jsn-upgrade-link" href="javascript:void()">
					<?php echo JText::_('JSN_TPLFW_UPGRADE_TO_PRO')." ".$nextEdition ?>
				</a>
				<?php endif ?>
			</h2>
			<div class="jsn-product-intro jsn-section-content">
				<div class="jsn-product-thumbnail">
					<a href="<?php echo $templateLink ?>" target="_blank">
						<img src="<?php echo JUri::root(true) ?>/templates/<?php echo $this->data->template ?>/template_preview.png"  class="jsn-rounded-medium jsn-box-shadow-medium"/>
					</a>
				</div>
				<div class="jsn-product-details">
					<dl>
						<dt><?php echo JText::_('JSN_TPLFW_TEMPLATE_DETAILS_COPYRIGHT') ?></dt>
						<dd>
							<?php echo JText::_('JSN_TPLFW_TEMPLATE_DETAILS_COPYRIGHT_TEXT') ?> -
							<a target="_blank" title="JoomlaShine.com" href="http://www.joomlashine.com">JoomlaShine.com</a>
						</dd>

						<dt><?php echo JText::_('JSN_TPLFW_TEMPLATE_DETAILS_INTEGRITY') ?></dt>
						<dd id="jsn-integrity">
							<span class="jsn-integrity-check">
								<a class="btn btn-mini" href="#check-integrity"><?php echo JText::_('JSN_TPLFW_TEMPLATE_DETAILS_INTEGRITY_CHECK') ?></a>
							</span>
							<span class="jsn-integrity-changed hide">
								Some files have been modified.
								<a class="btn btn-mini" href="javascript:void(0);">See details</a>
								<a class="btn btn-mini" href="<?php echo JRoute::_('index.php?widget=integrity&action=download&template=' . $this->data->template); ?>">Download</a>
							</span>
							<span class="jsn-integrity-notchange hide">
								No files modification found. <a class="btn btn-mini" href="#check-integrity">Check again</a>
							</span>
							<span class="jsn-integrity-status hide">
								Checking...
							</span>
						</dd>

						<dt><?php echo JText::_('JSN_TPLFW_TEMPLATE_DETAILS_VERSION') ?></dt>
						<dd id="jsn-version-info">
							<div data-target="framework" class="jsn-framework-version jsn-version-checking">
								<strong class="jsn-current-version">Framework:</strong> <?php echo $frameworkManifest->version ?> -
								<span class="jsn-status"><?php echo JText::_('JSN_TPLFW_TEMPLATE_DETAILS_CHECK_UPDATE') ?></span>
								<a class="jsn-update-link btn btn-mini" data-target="framework" href="javascript:void(0)">
									<?php echo JText::_('JSN_TPLFW_TEMPLATE_DETAILS_UPDATE_TO') ?>
									<span class="jsn-new-version"></span>
								</a>
							</div>
							<div data-target="template" class="jsn-template-version jsn-version-checking">
								<strong class="jsn-current-version">Template:</strong> <?php echo $templateManifest->version ?> -
								<span class="jsn-status"><?php echo JText::_('JSN_TPLFW_TEMPLATE_DETAILS_CHECK_UPDATE') ?></span>
								<a class="jsn-update-link btn btn-mini" data-target="template" href="javascript:void(0)">
									<?php echo JText::_('JSN_TPLFW_TEMPLATE_DETAILS_UPDATE_TO') ?>
									<span class="jsn-new-version"></span>
								</a>
							</div>
						</dd>
					</dl>
				</div>
				<div class="clearbreak"></div>
			</div>
			<div class="jsn-product-cta jsn-bgpattern pattern-sidebar">
				<div class="pull-left">
					<ul class="jsn-list-horizontal">
						<li><a id="jsn-about-promotion-modal" class="btn" href="http://www.joomlashine.com/free-joomla-templates-promo.html"><i class="icon-briefcase"></i>&nbsp;See other products</a></li>
					</ul>
				</div>

				<div class="pull-right">
					<ul class="jsn-list-horizontal">
						<li>
							<a class="jsn-icon24 jsn-icon-social jsn-icon-facebook" href="http://www.facebook.com/joomlashine" title="Connect with us on Facebook" target="_blank"></a>
						</li>
						<li>
							<a class="jsn-icon24 jsn-icon-social jsn-icon-twitter" href="http://www.twitter.com/joomlashine" title="Follow us on Twitter" target="_blank"></a>
						</li>
						<li>
							<a class="jsn-icon24 jsn-icon-social jsn-icon-youtube" href="http://www.youtube.com/joomlashine" title="Watch us on YouTube" target="_blank"></a>
						</li>
					</ul>
				</div>
				<div class="clearbreak"></div>
			</div>
		</div>
	</div>
</div>

<?php $backupFile = $this->findLatestBackup($this->data->template) ?>
<?php if (is_file($backupFile)): ?>
<div id="jsn-backup-file" class="row-fluid">
	<div class="span12 alert alert-block">
		<h4><?php echo JText::_('JSN_TPLFW_DOWNLOAD_MODIFIED_FILES') ?>
			<a href="<?php echo JUri::root(true) ?>/templates/<?php echo $this->data->template ?>/backup/<?php echo basename($backupFile) ?>" class="btn btn-large pull-right"><?php echo JText::_('JSN_TPLFW_DOWNLOAD_FILE') ?></a>
		</h4>
		<p><?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_INSTALL_DOWNLOAD_BACKUP') ?></p>
	</div>
</div>
<?php endif ?>