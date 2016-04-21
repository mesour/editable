/**
 * Mesour Popover - mesour.popover.js
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour.popover = !mesour.popover ? {} : mesour.popover;

(function($) {

	var Popover = function() {

		var _this = this;

		this.create = function(element, options) {
			element.popover(!options ? {} : options);
		};

		this.show = function(element, onInserted, persist) {
			if(typeof onInserted === 'function') {
				if(persist) {
					element.on('shown.bs.popover', onInserted);
				} else {
					element.off('shown.bs.popover.non-persist');
					element.on('shown.bs.popover.non-persist', onInserted);
				}
			}
			element.popover('show');
		};

		this.hide = function(element) {
			element.popover('hide');
		};

		this.destroy = function(element) {
			element.popover('destroy');
		};

		this.getTip = function(element) {
			return element.data('bs.popover').$tip;
		};

	};

	mesour.core.createWidget('popover', new Popover());
})(jQuery);