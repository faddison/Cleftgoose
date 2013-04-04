define([
	'jquery'
],

function ($) {
	var JSNDropMenu = function(params)
	{
		this.params = $.extend({}, params);

		// Get dropdown menu element
		if ((this.$menu = $('ul.jsn-menu')).length) {
			// Set event handler
			$(document).ready($.proxy(function() {
				this.initialize();
			}, this));
		}
	};

	JSNDropMenu.prototype = {
		initialize: function() {
			var $parent = this.$menu.find('li.menu-name, li.parent');

			$parent.bind('mouseenter', function() {
				if ($(this).hasClass('menu-name') || $(this).hasClass('parent')) {
					var $sub = $(this.querySelector('.jsn-submenu'));

					this.JSNDropMenuPosition = this.JSNDropMenuPosition || {
						left: $sub.css('left'),
						'margin-top': $sub.css('margin-top'),
						'margin-left': $sub.css('margin-left')
					};
				}
			});	

			$parent.bind('mouseleave', function() {
				if (this.JSNDropMenuPosition) {
					var $sub = $(this.querySelector('.jsn-submenu')).css(this.JSNDropMenuPosition);

					// Schedule sub-menu hiding
					setTimeout(function() {
						$sub.css({
							left: '',
							'margin-top': '',
							'margin-left': ''
						});
					}, 500);
				}
			});	
		}
	};

	return JSNDropMenu;
});
