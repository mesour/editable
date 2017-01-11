/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function($) {

	mesour._editable.fields.ManyToOne = function(fieldStructure, editable, element, parameters, identifier, value, forceForm) {

		this.TYPE = editable.TYPE_MANY_TO_ONE;

		parameters = parameters || {};

		var _this = this,
			select,
			oldValue = element.text(),
			title = fieldStructure['title'],
			reference = fieldStructure['reference'],
			table = reference['table'],
			primaryKey = reference['primary_key'],
			column = reference['column'],
			editButton = $('<button class="btn btn-info" title="' + editable.getEditableWidget().getTranslate('editItem') + '"><i class="fa fa-pencil"></i></button>'),
			pattern = reference['pattern'],
			fieldName = fieldStructure['name'],
			createNewRow = parameters['create_new_row'],
			editCurrentRow = parameters['edit_current_row'];

		var popover;
		editable.getEditableWidget().getReferenceData(editable.getName(), table, function(data) {
			data = data.data ? data.data : data;

			var values = {};

			if (createNewRow) {
				values[0] = {
					key: '__add_new_record__',
					name: '+ Add new record'
				};
			}

			var found = null;
			for (var i in data) {
				if (!data.hasOwnProperty(i)) {
					continue;
				}
				if (data[i][primaryKey] == value) {
					found = data[i];
				}

				values[data[i][primaryKey]] = {
					key: data[i][primaryKey],
					name: pattern ? mesour.core.parseValue(pattern, data[i]) : data[i][primaryKey]
				};
			}

			select = new mesour._editable.fields.Enum(fieldStructure, editable, element, parameters, identifier, value, values);
			popover = select.getEditablePopover();

			if (editCurrentRow) {
				popover.addButton(editButton);
			}

			var createForm = function(formValues) {
				popover.hide();

				editable.getModal().show();
				editable.getModal().setTitle(title);

				var elementFields = editable.getElementStructure(table);
				var form = editable.getModal().createForm(elementFields.fields ? elementFields.fields : elementFields);

				if (formValues) {
					if (!found) {
						throw new Error('Item not found.');
					}
					editable.getModal().addHiddenField(form, 'id', identifier, true);
					editable.getModal().fillForm(form, found);
				}

				editable.getModal().addHiddenField(form, '__fieldName', fieldName);
				editable.getModal().addHiddenField(form, '__identifier', identifier);

				editable.getModal().onSubmit(identifier, function(e, currentIdentifier) {
					_this.getValue = function() {
						var out = select.getValue();
						out['newValues'] = editable.getModal().getFormValues(form);
						if (!found) {
							out['oldValues'] = {};
							out['oldValues'][primaryKey] = value;
						} else {
							out['oldValues'] = found;
						}
						return out;
					};

					if (formValues) {
						editable.editForm(fieldName, currentIdentifier, form, table);
					} else {
						editable.create(fieldName, currentIdentifier, form, table);
					}

					_this.getValue = function() {
						return select.getValue();
					};
				});
			};

			select.getSelect().on('change', function() {
				var $this = $(this);
				if ($this.val() === '__add_new_record__') {
					createForm();
				}
			});

			if (forceForm) {
				createForm(true);
			}

			editButton.on('click', function(e) {
				e.preventDefault();

				editable.close(fieldName, identifier, true);
				editable.edit(fieldName, element, identifier, value, true);
			});

			popover.onSave(function() {
				var $this = $(this).closest('.input-group').find(':input:first');
				if ($this.val() === '__add_new_record__') {
					createForm();
				} else {
					editable.save(fieldName, identifier);
				}
			});
		});

		this.getElement = function() {
			return element;
		};

		this.getValue = function() {
			return select.getValue();
		};

		this.reset = function() {
			popover.destroy();
			select.reset();
		};

		this.save = function() {
			select.save();
		};

	};

})(jQuery);