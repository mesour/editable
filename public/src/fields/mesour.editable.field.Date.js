/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function($) {

	mesour._editable.fields.Date = function(fieldStructure, editable, element, parameters, identifier, value) {

		this.TYPE = editable.TYPE_DATE;

		parameters = parameters || {};

		var text = new mesour._editable.fields.Text(fieldStructure, editable, element, parameters, identifier, true);

		var _this = this,
			fieldName = fieldStructure['name'],
			format = fieldStructure['format'],
			input = text.getInput();

		element.parent().css('position', 'relative');

		var oldValue = value;
		input.val(!oldValue || oldValue === '-' ? '' : oldValue);
		input.attr('data-is-date', 'true');
		input.attr('placeholder', format);

		mesour.dateTimePicker.create(input, format, true);
		mesour.dateTimePicker.show(input);

		this.getElement = function() {
			return element;
		};

		this.getValue = function() {
			var out = text.getValue();
			out['oldValue'] = oldValue;
			return out;
		};

		this.reset = function() {
			mesour.dateTimePicker.destroy(input);
			text.reset();
		};

		this.save = function() {
			mesour.dateTimePicker.destroy(input);
			text.save();
		};

	};

})(jQuery);