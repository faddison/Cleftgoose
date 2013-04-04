!function ($) {
	"use strict";

	var	JSNTemplateUpdate = function (button, params)
	{
		var self = this;

		this.defaultParams = {
			basePath: '/',
			title: 'Update Template to the latest version',
			width: 800,
			height: 600
		};

		this.modalButtons = {
			'Close': function () {
				self.panel.dialog('close');
			}
		};

		this.params = $.extend(this.defaultParams, params);
		this.button = $(button);
		this.panel  = $('<div />', { 'class': 'jsn-template-update jsn-bootstrap' });

		function init () {
			// Initialize modal window
			self.panel
				.dialog({
					width: self.params.width,
					height: self.params.height,
					title: self.params.title,
					resizable: false,
					draggable: false,
					autoOpen : false,
					modal: true,
					open: loadConfirmScreen,
					closeOnEscape: false,
					buttons: self.modalButtons
				});

			// Handle click event for install button to open
			// dialog
			self.button.on('click', function (e) {
				e.preventDefault();

				self.target = $(this).attr('data-target');
				self.target == 'framework'
					? self.panel.dialog('option', 'title', 'Update Framework to the latest version')
					: self.panel.dialog('option', 'title', 'Update Template to the latest version');

				self.panel.empty()
					.dialog('open');
			});

			self.panel.delegate('button[id^="btn-finish"]', 'click', function (e) {
				e.preventDefault();
				self.panel.dialog('close');
			});

			// Handle window resize event to update modal position
			$(window).on('resize', function () {
				self.panel.dialog('option', 'position', 'center');
			});

			checkVersionUpdate();
		}

		/**
		 * Start download package for automatic update
		 * @return void
		 */
		function downloadPackage (loginData)
		{
			var
			liDownload	= $('#jsn-download-package'),
			spanStatus	= liDownload.find('span.jsn-status'),
			btnFinish	= self.panel.find('#btn-finish-install');

			liDownload.removeClass('hide');

			$.ajax({
				url: 'index.php?widget=update&action=download&template=' + self.params.template,
				type: 'POST',
				dataType: 'JSON',
				data: loginData,
				success: function (response) {
					if (response.type == 'error') {
						liDownload.removeClass('jsn-loading').addClass('jsn-error');
						spanStatus.text(response.data).addClass('alert alert-error');
						btnFinish.removeAttr('disabled');

						return;
					}

					liDownload.removeClass('jsn-loading').addClass('jsn-success');
					installUpdate();
				}
			});
		}

		/**
		 * Request to create a list of user modified files
		 * 
		 * @return  void
		 */
		function checkFilesModification ()
		{
			var
			liCreateList = self.panel.find('#jsn-check-files-modification'),
			spanStatus   = liCreateList.find('span.jsn-status');

			$.getJSON('index.php?widget=integrity&action=check&template=' + self.params.template, function (response) {
				liCreateList.removeClass('jsn-loading');
				liCreateList.addClass('jsn-success');

				if (response.data.hasModification == true && $(response.data.files['edit']).size() > 0) {
					self.hasModification = true;
					backupModifiedFiles();
					return;
				}

				downloadPackage(self.customerInfo);
			});
		}

		/**
		 * Backup all modified files to a folder
		 * 
		 * @return  void
		 */
		function backupModifiedFiles ()
		{
			var
			liBackupList = self.panel.find('#jsn-backup-modified-files'),
			spanStatus   = liBackupList.find('span.jsn-status');

			// Show backup modified files task
			liBackupList.removeClass('hide');

			$.getJSON('index.php?widget=update&action=backup&template=' + self.params.template, function (response) {
				// Show backup modified files task
				liBackupList
					.removeClass('jsn-loading')
					.addClass('jsn-success');

				self.panel.find('#jsn-success-message a').attr('href', response.data);
				self.hasBackup = true;

				downloadPackage(self.customerInfo);
			});
		};

		function installUpdate ()
		{
			var
			liInstall		= self.panel.find('#jsn-install-update'),
			successMessage	= self.panel.find('#jsn-success-message'),
			spanStatus		= liInstall.find('span.jsn-status'),
			btnFinish		= self.panel.find('#btn-finish-install');

			liInstall.removeClass('hide');

			// Send request to install template update
			$.getJSON('index.php?widget=update&action=install-package&template=' + self.params.template, function (response) {
				if (response.type == 'success') {
					liInstall.removeClass('jsn-loading').addClass('jsn-success');
					successMessage.removeClass('hide');

					if (self.hasBackup !== undefined && self.hasBackup == true) {
						successMessage.find('#jsn-backup-information').removeClass('hide');
					}
				}
				else {
					liInstall.removeClass('jsn-loading').addClass('jsn-error');
					spanStatus
						.text(response.data)
						.addClass('alert alert-error');
				}
				
				btnFinish.removeAttr('disabled');
				btnFinish.on('click', function (e) {
					e.preventDefault();
					self.panel.dialog('close');
					window.location.reload();
				});
			});
		}

		/**
		 * Check update for current template
		 * 
		 * @return  void
		 */
		function checkVersionUpdate ()
		{
			// Send ajax request to receive update infor
			$.getJSON('index.php?widget=update&action=check-update&template=' + self.params.template, function (result) {
				var
				container = $('#jsn-version-info > div');
				container.each(function () {
					var	el		= $(this),
						status	= el.find('.jsn-status'),
						version	= el.find('.jsn-new-version');

					if (result.type == 'success') {
						var info = result.data[el.attr('data-target')];

						if (info.hasUpdate == true) {
							el.toggleClass('jsn-version-checking jsn-version-outdated');
							status.text('Outdated version');
							version.text(info.newVersion);
						} else {
							el.toggleClass('jsn-version-checking jsn-version-latest');
							status.text('The latest version.');
						}

						self.versionData = result.data;
					} else {
						status.text(result.data);
					}
				});
			});
		}

		/**
		 * Handle dialog opened event to load start
		 * page of sample data installation process
		 * 
		 * @return void
		 */
		function loadConfirmScreen ()
		{
			// Set loading state
			self.panel.addClass('jsn-loading');

			$.getJSON('index.php?widget=update&action=confirm&template=' + self.params.template + '&target=' + self.target, function (response) {
				self.panel.html(response.data);
				self.panel.removeClass('jsn-loading');

				var
				confirmUpdateButton = self.panel.find('button#btn-confirm-update'),
				customerInfoFields = self.panel.find('input[name="username"], input[name="password"]');

				if (customerInfoFields.size() == 0) {
					// Event for confirm button when current edition is FREE
					confirmUpdateButton.on('click', function () {
						confirmUpdateButton.attr('disabled', 'disabled');
						loadInstallScreen();
					});
				}
				else {
					var
					username = self.panel.find('input[name="username"]'),
					password = self.panel.find('input[name="password"]');

					customerInfoFields.on('keyup change', function () {
						self.customerInfo = {
							username: self.panel.find('input[name="username"]').val(),
							password: self.panel.find('input[name="password"]').val()
						};

						self.customerInfo.username != '' && self.customerInfo.password != ''
							? confirmUpdateButton.removeAttr('disabled')
							: confirmUpdateButton.attr('disabled', 'disabled');
					});

					confirmUpdateButton.on('click', function () {
						confirmUpdateButton.attr('disabled', 'disabled');

						// Send request to checking customer information
						$.ajax({
							url: 'index.php?widget=update&action=confirm&template=' + self.params.template,
							type: 'POST',
							dataType: 'JSON',
							data: {
								username: self.customerInfo.username,
								password: self.customerInfo.password
							},
							success: function (response) {
								if (response.type == 'success') {
									self.customerInfo = {
										username: self.customerInfo.username,
										password: self.customerInfo.password
									};

									loadInstallScreen();
								}
								else {
									// TODO: Show error message
								}
							}
						});
					});
				}
			});
		};

		/**
		 * Start process to install template framework latest version
		 * @return void
		 */
		function startInstallFramework ()
		{
			$.getJSON('index.php?widget=update&action=download-framework&template=' + self.params.template, function (response) {
				var
				downloadPackage	= self.panel.find('li#jsn-download-package'),
				downloadStatus	= downloadPackage.find('.jsn-status'),
				installUpdate	= self.panel.find('li#jsn-install-update'),
				installStatus	= installUpdate.find('.jsn-status');

				if (response.type == 'success') {
					downloadPackage.toggleClass('jsn-loading jsn-success');
					installUpdate.removeClass('hide');

					$.getJSON('index.php?widget=update&action=install-framework&template=' + self.params.template, function (result) {
						if (result.type == 'success') {
							var
							successMessage	= self.panel.find('#jsn-success-message');
							successMessage.removeClass('hide');
							installUpdate.toggleClass('jsn-loading jsn-success');
						}
						else {
							installUpdate.toggleClass('jsn-loading jsn-error');
							installStatus.text(result.data);
						}

						var
						finishButton = self.panel.find('#btn-finish-install');
						finishButton.removeAttr('disabled');
						finishButton.on('click', function () {
							self.panel.dialog('close');
							window.location.reload();
						});
					});
				}
				else {
					downloadPackage.toggleClass('jsn-loading jsn-error');
					downloadStatus.text(response.data);
				}
			});
		};

		/**
		 * Load install update screen and start download process
		 * 
		 * @return  void
		 */
		function loadInstallScreen ()
		{
			self.panel.dialog('option', 'buttons', {});

			$.getJSON('index.php?widget=update&action=install&template=' + self.params.template + '&target=' + self.target, function (response) {
				self.panel.html(response.data);

				if (self.target == 'framework') {
					startInstallFramework();
					return;
				}

				if (self.versionData['framework'].hasUpdate === true) {
					var
					downloadPackage	= self.panel.find('li#jsn-download-framework'),
					downloadStatus	= downloadPackage.find('.jsn-status'),
					installUpdate	= self.panel.find('li#jsn-install-framework'),
					installStatus	= installUpdate.find('.jsn-status');

					downloadPackage.removeClass('hide');

					// Install framework update
					$.getJSON('index.php?widget=update&action=download-framework&template=' + self.params.template, function (response) {
						if (response.type == 'success') {
							downloadPackage.toggleClass('jsn-loading jsn-success');
							installUpdate.removeClass('hide');

							$.getJSON('index.php?widget=update&action=install-framework&template=' + self.params.template, function (result) {
								if (result.type == 'success') {
									installUpdate.toggleClass('jsn-loading jsn-success');
									checkFilesModification();
								}
								else {
									installUpdate.toggleClass('jsn-loading jsn-error');
									installStatus.text(result.data);

									var
									finishButton = self.panel.find('#btn-finish-install');
									finishButton.removeAttr('disabled');
									finishButton.on('click', function () {
										self.panel.dialog('close');
										window.location.reload();
									});
								}
							});
						}
						else {
							downloadPackage.toggleClass('jsn-loading jsn-error');
							downloadStatus.text(response.data);
						}
					});
				}
				else {
					checkFilesModification();
				}
			});
		};

		function finishUpdate ()
		{
			self.panel.dialog('close');
		};

		init();
	};

	/**
	 * Register jQuery plugin
	 *
	 * @param   element  button  Button that will triggered event to start install sample data
	 * @param   object   params  Object parameters
	 * 
	 * @return  void
	 */
	$.initTemplateUpdate = function (button, params) {
		if ($.__template_admin_auto_update__ === undefined)
			$.__template_admin_auto_update__ = new JSNTemplateUpdate(button, params);
	};
}(jQuery);