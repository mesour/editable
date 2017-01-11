/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function($) {

	mesour._editable.fields.Text = function(fieldStructure, editable, element, parameters, identifier, isSpecial) {

		this.TYPE = editable.TYPE_TEXT;

		parameters = parameters || {};

		var _this = this,
			fieldName = fieldStructure['name'],
			hasTextarea = !isSpecial ? (fieldStructure['hasTextarea'] === 'false' ? false : true) : false,
			oldValue = $.trim(element.text()),
			input,
			hasSoftReset = true,
			reset = function() {
			};

		if (hasTextarea) {
			input = $('<textarea type="text" class="form-control" name="' + fieldName + '"></textarea>');
			input.text(oldValue);
			hasSoftReset = false;
		} else {
			input = $('<input type="text" value="' + oldValue + '" class="form-control" name="' + fieldName + '">');
			input.on('keydown.mesour-editable', function(e) {
				if (e.keyCode === 13) {
					editable.save(fieldName, identifier);
				} else if (e.keyCode === 27) {
					reset();
				}
			});
		}

		var popover = new mesour._editable.EditablePopover(editable, element, input, _this, hasSoftReset),
			reset = function() {
				popover.destroy();
			};

		if (hasTextarea) {
			input.on('keydown', editable.textareaTabFix);
		}

		input.css('width', '100%');

		popover.onSave(function() {
			editable.save(fieldName, identifier);
		});
		popover.onReset(function() {
			reset();
		});

		input.focus();

		this.getInput = function() {
			return input;
		};

		this.getEditablePopover = function() {
			return popover;
		};

		this.getElement = function() {
			return element;
		};

		this.getValue = function() {
			return {
				oldValue: oldValue,
				value: input.val(),
				params: parameters
			};
		};

		this.reset = function() {
			reset();
		};

		this.save = function() {
			popover.destroy();
			element.empty().text(input.val());
		};

	};

})(jQuery);