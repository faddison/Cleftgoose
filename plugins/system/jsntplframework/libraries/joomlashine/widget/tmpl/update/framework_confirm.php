<p><?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_FRAMEWORK_AUTH_DESC') ?></p>
<div class="alert alert-warning">
	<span class="label label-important"><?php echo JText::_('JSN_TPLFW_IMPORTANT_INFORMATION') ?></span>
	<ul>
		<li><?php echo JText::_('JSN_TPLFW_AUTO_UPDATE_AUTH_NOTE_01') ?></li>
	</ul>
</div>

<form id="jsn-confirm-update" class="form-inline">
	<div class="jsn-toolbar">
		<button id="btn-confirm-update" class="btn btn-primary" type="button"><?php echo JText::_('JSN_TPLFW_UPDATE') ?></button>
	</div>
	
	<?php echo JHtml::_('form.token') ?>
</form>
