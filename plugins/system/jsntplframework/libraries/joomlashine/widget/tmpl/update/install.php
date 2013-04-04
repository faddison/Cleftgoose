<p><?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_INSTALLATION_DESC') ?></p>

<form id="jsn-update-install" action="index.php?widget=update&action=load-package">
	<ul id="jsn-update-processes">
		<li id="jsn-download-framework" class="jsn-loading hide">
			<span class="jsn-title"><?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_FRAMEWORK_DOWNLOAD_PACKAGE') ?> <i class="jsn-icon16 jsn-icon-status"></i></span>
			<span class="jsn-status"></span>
		</li>
		<li id="jsn-install-framework" class="jsn-loading hide">
			<span class="jsn-title"><?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_FRAMEWORK_INSTALL') ?> <i class="jsn-icon16 jsn-icon-status"></i></span>
			<span class="jsn-status"></span>
		</li>
		<li id="jsn-check-files-modification" class="jsn-loading hide">
			<span class="jsn-title"><?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_CHECK_FILES_MODIFIED') ?> <i class="jsn-icon16 jsn-icon-status"></i></span>
			<span class="jsn-status"></span>
		</li>
		<li id="jsn-backup-modified-files" class="jsn-loading hide">
			<span class="jsn-title"><?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_CREATE_LIST_MODIFIED') ?> <i class="jsn-icon16 jsn-icon-status"></i></span>
			<span class="jsn-status"></span>
		</li>
		<li id="jsn-download-package" class="jsn-loading hide">
			<span class="jsn-title"><?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_DOWNLOAD_PACKAGE') ?> <i class="jsn-icon16 jsn-icon-status"></i></span>
			<span class="jsn-status"></span>
		</li>
		<li id="jsn-install-update" class="jsn-loading hide">
			<span class="jsn-title"><?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_INSTALL') ?> <i class="jsn-icon16 jsn-icon-status"></i></span>
			<span class="jsn-status"></span>
		</li>
	</ul>

	<div id="jsn-success-message" class="hide">
		<h3><?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_INSTALL_SUCCESS') ?></h3>
		<p><?php echo JText::sprintf('JSN_TPLFW_AUTO_UPDATE_INSTALL_SUCCESS_DESC', $template['realName']) ?></p>

		<div id="jsn-backup-information" class="alert alert-warning hide">
			<span class="label label-important">Important information</span>
			<p>
				<?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_INSTALL_DOWNLOAD_BACKUP') ?>
				<a href="" class="btn"><?php echo JText::_('JSN_TPLFW_DOWNLOAD_FILE') ?></a>
			</p>
		</div>
	</div>

	<hr />
	<div class="jsn-toolbar">
		<button id="btn-finish-install" class="btn btn-primary" disabled="disabled"><?php echo JText::_('JSN_TPLFW_FINISH') ?></button>
	</div>
</form>
