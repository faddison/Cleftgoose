!function ($) {
	"use strict";

	function JSNSampleDataInstallation (button, params)
	{
		var
		self = this;
		self.params = $.extend({
			basePath: '/',
			title	: 'Install Sample Data',
			width	: 800,
			height	: 600
		}, params);

		// Sample data dialog options
		self.dialogOptions = {
			width		: 800,
			height		: 600,
			title		: self.params.title,
			resizable	: false,
			draggable	: false,
			autoOpen	: false,
			modal		: true,
			open		: loadStartScreen,
			closeOnEscape: false,
			buttons		: {
				'Close': function () {
					self.panel.dialog('close');
				}
			}
		};

		self.button = $(button);
		self.panel  = $('<div />', { 'class': 'jsn-sample-data jsn-bootstrap' });

		// Initialize modal window
		self.panel.dialog(self.dialogOptions);

		/**
		 * Initialize sample data installation
		 * 
		 * @return void
		 */
		function init ()
		{
			// Handle click event for install button to open dialog
			self.button.on('click', function (e) {
				e.preventDefault();
				self.panel.html('');
				self.panel.dialog('open');
			});

			// Handle window resize event to update modal position
			$(window).on('resize', function () {
				self.panel.dialog('option', 'position', 'center');
			});
		};

		function showCloseButton ()
		{
			self.panel.dialog('option', 'buttons', {
				'Close': function () {
					self.panel.dialog('close');
				}
			});
		}

		/**
		 * Generate HTML markup for display all extensions
		 * that allow user choose which will be installed
		 * 
		 * @return void
		 */
		function createExtensionList (extensions)
		{
			var
			extensionContainerEl	= self.panel.find('#jsn-list-extensions'),
			extensionListEl			= extensionContainerEl.find('ul');
			
			$.map(extensions, function (extension) {
				// Create extension item
				var
				childList	= $('<ul>');
				
				if (extension.depends !== undefined) {
					$.map(extension.depends, function (childExtension) {
						if (childExtension.state == 'installed')
							return;

						var
						childListItem = createExtensionItem(childExtension, true);
						childList.append(childListItem);
					});

					childList.append(
						$('<li />', { 'class': 'jsn-clean-up hide', 'data-task': 'clean-up' })
							.append($('<span />', { 'class': 'jsn-title', 'text': 'Cleaning' }))
							.append($('<span />', { 'class': 'jsn-status' }))
					);
				}

				if (childList.children().size() > 1 || extension.state != 'installed') {
					var
					listItem = createExtensionItem(extension, extension.state != 'install');
					listItem.append(childList);
					listItem.appendTo(extensionListEl);
				}
			});

			if (extensionListEl.find('input[type="checkbox"]').size() == 0) {
				installSampleData();
			}
			else {
				extensionContainerEl.removeClass('hide');
				self.panel.find('#btn-confirm-install').removeAttr('disabled');
			}
		};

		/**
		 * Method to generate html markup for item that display on
		 * extension list
		 * 
		 * @param   object   extension         Extension information object
		 * @param   boolean  disabledCheckbox  Flag to disable checkbox
		 * @return  DOMElement
		 */
		function createExtensionItem (extension, disabledCheckbox)
		{
			// Create extension item
			var
			listItem			= $('<li />', { 'data-type': extension.identifiedname, 'class': 'jsn-extension' }),
			listItemLabel		= $('<label />', { 'class': 'checkbox jsn-extension-label jsn-title', 'text': ' ' + extension.description + ' ' }),
			listItemCheckbox	= $('<input />', { 'type': 'checkbox', 'checked': 'checked', 'value': extension.identifiedname }),
			listItemTask		= $('<span />', { 'class': 'label', 'text': 'Installed' }),
			listItemStatus		= $('<span />', { 'class': 'jsn-status', 'text': '' }),
			listItemIcon 		= $('<i />', { 'class': 'jsn-icon-status' });

			switch (extension.state) {
				case 'install':
					listItemTask.text('New Installation').addClass('label-success');
					listItemCheckbox.addClass('jsn-install');
					break;
				case 'update':
					listItemTask.text('Update').addClass('label-warning');
					listItemCheckbox.addClass('jsn-install');
					break;
			}

			if (disabledCheckbox !== undefined && disabledCheckbox == true) {
				listItemCheckbox.attr('disabled', 'disabled');
			}
			
			listItemLabel
				.prepend(listItemCheckbox)
				.append(listItemIcon)
				.prependTo(listItem);

			listItem
				.append(listItemTask)
				.append(listItemStatus);

			// Add elements to display product description
			if (extension.productdesc !== undefined) {
				var
				listItemToggleDesc	= $('<a />', { 'class': 'jsn-extension-details', 'href': '#' }),
				listItemDescription = $('<p />', { 'class': 'jsn-exttension-desc hide', 'text': extension.productdesc }),
				listItemToggler		= $('<i />', { 'class': 'icon-plus-sign' });

				listItemToggleDesc
					.append(listItemToggler)
					.insertAfter(listItemLabel)
					.on('click', function (e) {
						e.preventDefault();
						listItemToggler.toggleClass('icon-plus-sign, icon-minus-sign');
						listItemDescription.toggleClass('hide');
					});

				listItemDescription
					.append($('<a />', { 'href': extension.producturl, 'target': '_blank', 'class': 'jsn-read-more', 'text': 'Read more...' }))
					.appendTo(listItem);
			}

			listItemCheckbox.on('change', function () {
				var
				el = $(this),
				childList = listItem.find('ul'),
				childListCheckboxes = childList.find('input[type="checkbox"]');

				el.is(':checked')
					? childListCheckboxes.attr('checked', 'checked')
					: childListCheckboxes.removeAttr('checked');
			});

			return listItem;
		};

		/**
		 * Load installation screen
		 * 
		 * @return void
		 */
		function loadInstallScreen ()
		{
			// Set loading state
			self.panel.addClass('jsn-loading');

			$.getJSON('index.php?widget=sample&action=install&template=' + self.params.template, function (response) {
				self.panel.html(response.data);
				self.panel.removeClass('jsn-loading');

				// Send request to download sample data package
				$.getJSON('index.php?widget=sample&action=download-package&template=' + self.params.template, function (packageResponse) {
					var
					btnInstall			= self.panel.find('#btn-confirm-install'),
					downloadPackageEl	= self.panel.find('#jsn-download-package'),
					downloadStatusEl	= downloadPackageEl.find('.jsn-status');
					downloadPackageEl.removeClass('jsn-loading')

					if (packageResponse.type == 'success') {
						downloadPackageEl.addClass('jsn-success');
						createExtensionList(packageResponse.data);
						
						// btnInstall.removeAttr('disabled');
						btnInstall.on('click', function (e) {
							e.preventDefault();
							btnInstall.attr('disabled', 'disabled');

							var
							listExtensionEl		= self.panel.find('#jsn-list-extensions'),
							selectedExtensions	= listExtensionEl.find('input:checked');

							listExtensionEl.addClass('jsn-processing');

							if (selectedExtensions.size() > 0) {
								listExtensionEl.find('li').each(function () {
									var li = $(this);
									if (li.find('input:checked').size() == 0)
										li.addClass('hide');
								});

								listExtensionEl.find('input:checked.jsn-install').each(function () {
									$(this).closest('li').addClass('jsn-pending');
								});

								installExtensions();
							}
							else {
								installSampleData();
							}
						});
					}
					else {
						downloadPackageEl.addClass('jsn-error');
						downloadStatusEl
							.text(packageResponse.data)
							.addClass('alert alert-error');
						switchToManualInstall();
						showCloseButton();
					}
				});
			});
		};

		/**
		 * Handle dialog opened event to load start
		 * page of sample data installation process
		 * 
		 * @return void
		 */
		function loadStartScreen ()
		{
			// Set loading state
			self.panel.addClass('jsn-loading');

			$.getJSON('index.php?widget=sample&action=confirm&template=' + self.params.template, function (response) {
				self.panel.html(response.data);
				self.panel.removeClass('jsn-loading');

				var
				installButton = self.panel.find('#btn-install'),
				agreeCheckbox = self.panel.find('input[name="agree"]');
				agreeCheckbox.on('click', function () {
					agreeCheckbox.is(':checked')
						? installButton.removeAttr('disabled')
						: installButton.attr('disabled', 'disabled');
				});

				installButton.on('click', function () {
					if (agreeCheckbox.is(':checked')) {
						self.panel.dialog('option', 'buttons', {});
						loadInstallScreen();
					}
				});
			});
		};

		/**
		 * Create a AJAX request to install sample data
		 * after install selected extensions
		 * 
		 * @return  void
		 */
		function installSampleData ()
		{
			var
			installDataEl		= self.panel.find('#jsn-install-data'),
			statusEl			= installDataEl.find('.jsn-status'),
			successMessageEl	= self.panel.find('#jsn-success-message'),
			btnFinish			= self.panel.find('#btn-finish-install'),
			btnInstall			= self.panel.find('#btn-confirm-install');

			installDataEl
				.removeClass('hide')
				.addClass('jsn-loading');

			// Register event handler to finish sample data
			// installation process
			btnFinish.on('click', function (e) {
				e.preventDefault();
				self.panel.dialog('close');
			});

			$.getJSON('index.php?widget=sample&action=install-data&template=' + self.params.template + '&styleId=' + self.params.styleId, function (response) {
				installDataEl.removeClass('jsn-loading');
				btnInstall.addClass('hide');

				if (typeof(response.data.outOfDate) != 'undefined' && response.data.outOfDate == true) {
					installDataEl.addClass('jsn-error');
					statusEl.text(response.data.message).addClass('alert alert-error');
					btnFinish.removeClass('hide');

					return;
				}
				
				if (processInstallDataResult(response) == false) {
					installDataEl.addClass('jsn-error');
					statusEl.text(response.data).addClass('alert alert-error');
					switchToManualInstall();
					showCloseButton();
				}
			});
		};

		/**
		 * Process reponsed data after sent install sample data request
		 * 
		 * @param   object  response  Reponse object returned from the server
		 * @return  boolean
		 */
		function processInstallDataResult (response)
		{
			var
			installDataEl		= self.panel.find('#jsn-install-data'),
			successMessageEl	= self.panel.find('#jsn-success-message'),
			btnFinish			= self.panel.find('#btn-finish-install'),
			btnInstall			= self.panel.find('#btn-confirm-install, #btn-manual-install');

			if (response.type == 'success') {
				installDataEl.addClass('jsn-success');
				successMessageEl.removeClass('hide');

				if (response.data != null && response.data.attention !== undefined && response.data.attention.length > 0) {
					var
					attentionEl		= self.panel.find('#jsn-attention'),
					attentionListEl	= attentionEl.find('ul'),
					dummyEl			= self.panel.find('#jsn-attension-dummy');
					attentionEl.removeClass('hide');

					$(response.data.attention).each(function (index, info) {
						var
						el = dummyEl.clone();
						el.find('strong').text(info.name);
						el.find('a').attr('href', info.url);
						el.attr('id', 'jsn-' + info.id).removeClass('hide');
						el.appendTo(attentionListEl);
					});
				}

				btnInstall.addClass('hide');
				btnFinish
					.removeClass('hide')
					.removeAttr('disabled')
					.on('click', function (e) {
						e.preventDefault();
						self.panel.dialog('close');
					});;

				return true;
			}

			return false;
		}

		/**
		 * Handling manual install sample data process
		 * 
		 * @return  void
		 */
		function switchToManualInstall ()
		{
			var
			installButton = self.panel.find('#btn-confirm-install'),
			installSampleData = self.panel.find('#jsn-install-data'),
			manualInstallButton = self.panel.find('#btn-manual-install'),
			manualInstallPanel = self.panel.find('#jsn-manual-install'),
			manualInstallForm = manualInstallPanel.find('form');

			manualInstallPanel.removeClass('hide');
			manualInstallButton.removeClass('hide');
			installButton.addClass('hide');

			// Declare callback function
			window.uploadSampleDataCallback = function (response) {
				processInstallDataResult(response);
				showCloseButton();
			};

			manualInstallPanel.find('input[type="file"]')
				.on('change', function () {
					$(this).val() != ''
						? manualInstallButton.removeAttr('disabled')
						: manualInstallButton.attr('disabled', 'disabled');
				});

			manualInstallButton
				.unbind('click')
				.bind('click', function (e) {
					manualInstallPanel.addClass('hide');
					manualInstallButton.attr('disabled', 'disabled');
					installSampleData
						.removeClass('hide')
						.addClass('jsn-loading');
					manualInstallForm.submit();
					self.panel.dialog('option', 'buttons', {});
				});
		}

		/**
		 * This method will be sent a request to install
		 * selected extensions
		 * 
		 * @return  void
		 */
		function installExtensions ()
		{
			var
			extensionEl = self.panel.find('#jsn-list-extensions li.jsn-pending:eq(0)');
			extensionEl
				.removeClass('jsn-pending')
				.addClass('jsn-loading');

			if (extensionEl.size() == 0) {
				installSampleData();
				return;
			}

			var
			extensionId = extensionEl.attr('data-type');

			if (self.panel.find('li[data-type="' + extensionId + '"].jsn-success').size() > 0) {
				extensionEl
					.removeClass('jsn-loading')
					.addClass('jsn-success');
					
				installExtensions();
				return;
			}
			
			$.getJSON('index.php?widget=sample&action=install-extension&id=' + extensionId + '&template=' + self.params.template + '&tool_redirect=0', function (response) {
				extensionEl
					.removeClass('jsn-loading')
					.addClass('jsn-' + response.type);

				if (response.type == 'error') {
					var
					extensionStatusEl = extensionEl.find('> .jsn-status'),
					extensionContainerEl = extensionStatusEl.closest('ul');

					extensionStatusEl
						.addClass('alert')
						.text(response.data);

					// Current extension is root element
					if (extensionContainerEl.attr('id') == 'jsn-root-extensions') {
						var
						childExtensionsEl = extensionStatusEl.closest('li').find('li.jsn-pending');
						markSkipped(childExtensionsEl);
					}
					else {
						var
						parentExtensionEl = extensionContainerEl.closest('li');
						markSkipped(parentExtensionEl.find('li.jsn-pending'));
					}

					removeErrorExtension(extensionEl);
					return;
				}

				var
				nextExtension = self.panel.find('#jsn-list-extensions li.jsn-pending:eq(0)');
				if (nextExtension.size() > 0) {
					installExtensions();
				}
				else {
					installSampleData();
				}
			});
		};

		/**
		 * Make a request to remove failed extension
		 * and related dependencies
		 * 
		 * @param   DOM  elm  DOM Element that contains extension information
		 * 
		 * @return  void
		 */
		function removeErrorExtension (elm)
		{
			var
			cleanUpEl = elm.find('ul > li.jsn-clean-up'),
			id = elm.attr('data-type');

			if (cleanUpEl.size() == 0)
				cleanUpEl = elm.parent().find('> li.jsn-clean-up');

			// Show clean up task to the screen
			cleanUpEl
				.addClass('jsn-loading')
				.removeClass('hide');

			cleanUpEl.parents('#jsn-root-extensions > li')
				.removeClass('jsn-success')
				.addClass('jsn-failed');

			// Make a request to clean up install failed extension data
			$.getJSON('index.php?widget=sample&action=clean-up&id=' + id + '&template=' + self.params.template, function (response) {
				cleanUpEl
					.removeClass('jsn-loading')
					.addClass('jsn-success');

				installExtensions();
			});
		};

		/**
		 * Mark all elements to skipped by add class "jsn-skipped"
		 * 
		 * @param   array  elements  Elements that will be added class "jsn-skipped"
		 * @return  void
		 */
		function markSkipped (elements)
		{
			elements
				.removeClass('jsn-pending')
				.addClass('jsn-skipped');

			elements.find('.jsn-status')
				.addClass('badge')
				.text('skipped');
		};

		// Initialize object
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
	$.initSampleDataInstallation = function (button, params) {
		if ($.__template_admin_sample_data__ === undefined)
			$.__template_admin_sample_data__ = new JSNSampleDataInstallation(button, params);
	};
}(jQuery)