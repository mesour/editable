/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function($) {

	mesour._editable.fields.Bool = function(fieldStructure, editable, element, parameters, identifier, value) {

		this.TYPE = editable.TYPE_TEXT;

		parameters = parameters || {};

		var _this = this,
			fieldName = fieldStructure['name'],
			description = fieldStructure['description'],
			nullable = fieldStructure['nullable'],
			oldValue = element.text(),
			prependButtons = [],
			input = $('<input type="checkbox" value="1" id="editable-bool">'),
			label = $('<label for="editable-bool">'),
			getInputValue = function() {
				return input.is(':checked');
			};

		if (nullable) {
			var emptyButton = $('<button class="btn btn-warning" title="'+editable.getEditableWidget().getTranslate('emptyButton')+'"><i class="fa fa-ban"></i></button>');
			prependButtons = [emptyButton];

			emptyButton.on('click', function(e) {
				e.preventDefault();

				if (confirm(editable.getEditableWidget().getTranslate('saveEmptyValue'))) {
					getInputValue = function() {
						return '';
					};
					input.prop('checked', false);
					editable.save(fieldName, identifier);
				}
			});
		}

		var popover = new mesour._editable.EditablePopover(editable, element, input, _this, false, prependButtons),
			reset = function() {
				popover.destroy();
			};

		popover.onSave(function(e) {
			editable.save(fieldName, identifier);
		});
		popover.onReset(function(e) {
			reset();
		});

		if (value == 1 || value === 'true' || value === true) {
			input.prop('checked', true);
		}

		input.wrap(label);
		input.after(' ' + description);

		this.getElement = function() {
			return element;
		};

		this.getInput = function() {
			return input;
		};

		this.getValue = function() {
			return {
				oldValue: value,
				value: getInputValue(),
				params: parameters
			};
		};

		this.reset = function() {
			reset();
		};

		this.save = function() {
			popover.destroy();
		};

	};

})(jQuery);