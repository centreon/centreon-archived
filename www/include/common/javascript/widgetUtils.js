(function($) {
	$.fn.styleTable = function(options) {
		var defaults = {
			css : 'styleTable'
		};
		options = $.extend(defaults, options);

		return this.each(function() {

			input = $(this);
			input.addClass(options.css);

			input.find("tr").live('mouseover mouseout', function(event) {
				if (event.type == 'mouseover') {
					$(this).children("td").addClass("ui-state-hover");
				} else {
					$(this).children("td").removeClass("ui-state-hover");
				}
			});

			input.find("th").addClass("ui-state-default");
			input.find("td").addClass("ui-widget-content");

			input.find("tr").each(function() {
				$(this).children("td:not(:first)").addClass("first");
				$(this).children("th:not(:first)").addClass("first");
			});
		});
	};
})(jQuery);
