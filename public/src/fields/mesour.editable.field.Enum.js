/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function($) {

	mesour._editable.fields.Enum = function(fieldStructure, editable, element, parameters, identifier, value, values, disableEmptyValue) {

		this.TYPE = editable.TYPE_ENUM;

		parameters = parameters || {};

		var _this = this,
			oldValue = element.text(),
			fieldName = fieldStructure['name'],
			values = values ? values : fieldStructure['values'],
			isNullable = fieldStructure['nullable'],
			removeRow = typeof parameters['remove_row'] !== 'undefined' ? (!parameters['remove_row'] ? false : true) : null,
			select = $('<select class="form-control"></select>'),
			popover = new mesour._editable.EditablePopover(editable, element, select, _this);

		select.css('width', '100%');

		for (var i in values) {
			if (!values.hasOwnProperty(i)) {
				continue;
			}
			var option = $('<option>')
				.attr('value', values[i]['key'])
				.text(values[i]['name']);

			if (values[i]['key'] == value) {
				option.prop('selected', true);
			}

			select.append(option);
		}

		if (isNullable && !disableEmptyValue) {
			select.prepend('<option value="">' + editable.getEditableWidget().getTranslate('emptyValue') + '</option>');
		}

		select.on('keydown.mesour-editable', function(e) {
			if (e.keyCode === 27) {
				_this.reset();
			}
		});

		popover.onSave(function() {
			editable.save(fieldName, identifier);
		});
		popover.onReset(function() {
			_this.reset();
		});

		this.getEditablePopover = function() {
			return popover;
		};

		this.getElement = function() {
			return element;
		};

		this.getSelect = function() {
			return select;
		};

		this.getValue = function() {
			return {
				oldValue: value,
				value: select.find(':selected').val(),
				params: parameters
			};
		};

		this.reset = function() {
			popover.destroy();
		};

		this.save = function() {
			popover.destroy();
			element.empty().text(select.find(':selected').text());
		};

	};

})(jQuery);