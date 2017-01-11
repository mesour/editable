/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function($) {

	mesour._editable.fields.OneToOne = function(fieldStructure, editable, element, parameters, identifier, value) {

		this.TYPE = editable.TYPE_ONE_TO_ONE;

		parameters = parameters || {};

		var _this = this,
			select,
			oldValue = element.text(),
			title = fieldStructure['title'],
			reference = fieldStructure['reference'],
			table = reference['table'],
			primaryKey = reference['primary_key'],
			column = reference['column'],
			fieldName = fieldStructure['name'],
			createNewRow = parameters['create_new_row'];

		function getDefaultValues(oldValues, values) {
			values = !values ? {} : values;
			oldValues = !oldValues ? {} : oldValues;
			var references = null;
			if (values['id']) {
				references = {
					id: values['id']
				};
			}
			return {
				oldValues: oldValues,
				newValues: values,
				params: parameters,
				values: references
			};
		};

		editable.getEditableWidget().getReferenceData(editable.getName(), table, function (data) {
			data = data.data ? data.data : data;

			var values = {},
				hiddenValues = {};

			for (var i in data) {
				if (!data.hasOwnProperty(i)) {
					continue;
				}

				if (data[i][primaryKey] == value) {
					values = data[i];
					hiddenValues[column] = data[i][primaryKey];
					break;
				}
			}

			editable.getModal().show();
			editable.getModal().setTitle(title);

			var elementFields = editable.getElementStructure(table);
			var form = editable.getModal().createForm(elementFields.fields ? elementFields.fields : elementFields);
			editable.getModal().fillForm(form, values, hiddenValues);
			editable.getModal().addHiddenField(form, '__fieldName', fieldName);

			if (identifier) {
				editable.getModal().addHiddenField(form, '__identifier', identifier);

				if(value) {
					var identifierField = editable.getModal().addHiddenField(form, primaryKey, value);
					identifierField.attr('data-editable-in-data', 'true');
				}
			}

			editable.getModal().onSubmit(identifier, function (e, currentIdentifier) {
				_this.getValue = function () {
					return getDefaultValues(values, editable.getModal().getFormValues(form));
				};

				if (value) {
					editable.editForm(fieldName, currentIdentifier, form, table);
				} else {
					editable.create(fieldName, identifier, form, table);
				}

				_this.getValue = function () {
					return getDefaultValues(values);
				};


			})
		});

		this.getElement = function() {
			return element;
		};

		this.getValue = function () {
			return getDefaultValues();
		};

		this.reset = function () {

		};

		this.save = function () {

		};

	};

})(jQuery);