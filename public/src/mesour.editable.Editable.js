/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;

(function($) {

	mesour._editable.Editable = function(name, data, editableWidget) {

		var modal = new mesour._editable.EditableModal(name, this),
			inline = false,
			disabledInlineAlerts = false,
			onComplete = function() {
			};

		var structure = data.fields || [];
		elements = data.elements || {};

		this.TYPE_TEXT = 'text';
		this.TYPE_NUMBER = 'number';
		this.TYPE_DATE = 'date';
		this.TYPE_ENUM = 'enum';
		this.TYPE_BOOL = 'bool';
		this.TYPE_ONE_TO_ONE = 'one_to_one';
		this.TYPE_MANY_TO_ONE = 'many_to_one';
		this.TYPE_ONE_TO_MANY = 'one_to_many';
		this.TYPE_MANY_TO_MANY = 'many_to_many';

		var _this = this,
			openedEdits = {};

		this.setInline = function(isInline) {
			inline = isInline === "true" ? true : false;
		};

		this.isInline = function() {
			return inline;
		};

		this.setDisabledInlineAlerts = function(isDisabled) {
			disabledInlineAlerts = isDisabled === "true" ? true : false;
		};

		this.isDisabledInlineAlerts = function() {
			return disabledInlineAlerts;
		};

		this.createAlert = function(message, type, dismissButton) {
			type = !type ? 'danger' : type;
			var $alert = $('<div class="alert alert-' + type + ' mesour-editable-alert">' + message + '</div>');
			if (typeof dismissButton === 'undefined' || dismissButton) {
				$alert.prepend('<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>');
			}
			return $alert;
		};

		function getFieldStructure(fieldName, need) {
			need = typeof need === 'undefined' ? true : need;
			for (var i = 0; i < structure.length; i++) {
				if (structure[i]['name'] === fieldName) {
					return structure[i];
				}
			}
			if (need) {
				throw new Error('No structure for field with name ' + fieldName);
			} else {
				return null;
			}
		};

		this.getFieldStructure = getFieldStructure;
		this.textareaTabFix = function(e) {
			if (e.keyCode === 9) {
				var start = this.selectionStart;
				var end = this.selectionEnd;

				var $this = $(this);
				var value = $this.val();

				$this.val(value.substring(0, start)
					+ "\t"
					+ value.substring(end));

				this.selectionStart = this.selectionEnd = start + 1;

				e.preventDefault();
			}
		};

		function removeEditedField(fieldName, identifier) {
			if (!openedEdits[fieldName]) {
				return;
			}
			if (identifier) {
				delete openedEdits[fieldName][identifier];
			} else {
				delete openedEdits[fieldName];
			}
		};

		function getEditedField(fieldName, identifier, need) {
			need = typeof need === "undefined" ? true : need;
			if (need && !openedEdits[fieldName]) {
				throw new Error('Field with name ' + fieldName + ' is not edited');
			}
			if (!openedEdits[fieldName])
				return null;
			if (identifier) {
				if (need && !openedEdits[fieldName][identifier]) {
					throw new Error('Field with name ' + fieldName + ' and identifier ' + identifier + ' is not edited');
				}
				return openedEdits[fieldName][identifier];
			} else {
				if (need && !openedEdits[fieldName]) {
					throw new Error('Field with name ' + fieldName + ' is not edited');
				}
				return openedEdits[fieldName];
			}
		};

		function editField(fieldStructure, element, identifier, value, forceForm) {
			var fieldName = fieldStructure['name'];

			for (var i in openedEdits) {
				if (!openedEdits.hasOwnProperty(i)) {
					continue;
				}
				if (openedEdits[i] instanceof mesour._editable.FieldEditor) {
					openedEdits[i].resetValue();
					delete openedEdits[i];
				} else {
					for (var j in openedEdits[i]) {
						if (!openedEdits[i].hasOwnProperty(j)) {
							continue;
						}
						openedEdits[i][j].resetValue();
						delete openedEdits[i][j];
					}
				}
			}

			modal.enable();

			if (identifier) {
				openedEdits[fieldName] = openedEdits[fieldName] || {};
				openedEdits[fieldName][identifier] = new mesour._editable.FieldEditor(_this, fieldStructure, element, identifier, value, forceForm);
			} else {
				openedEdits[fieldName] = new mesour._editable.FieldEditor(_this, fieldStructure, element, identifier, value, forceForm);
			}
		};

		this.getElementStructure = function(tableName) {
			if (!elements[tableName]) {
				throw new Error('Element with table name ' + tableName + ' not exist.');
			}
			return elements[tableName];
		};

		this.getEditableWidget = function() {
			return editableWidget;
		};

		this.setOnCompleteCallback = function(callback) {
			if (typeof callback !== 'function') {
				throw new Error('Callback must be function.');
			}
			onComplete = callback;
		};

		this.getModal = function() {
			return modal;
		};

		this.getName = function() {
			return name;
		};

		this.close = function(fieldName, identifier, removeEdited) {
			var field = getEditedField(fieldName, identifier, false);
			if (field) {
				field.resetValue();
			}
			if (removeEdited) {
				removeEditedField(fieldName, identifier);
			}
		};

		function postCallback(form, fieldName, table, field, response, identifier, isNormal) {
			try {
				var data = $.parseJSON(response.responseText);
				if (data.error) {
					if (isNormal) {
						var alertElement = _this.createAlert(data.error.message, 'danger', false),
							input;
						if (!_this.isInline()) {
							var popover = mesour.popover.getTip(field.getField()).find('.popover-content');
							popover.find('.mesour-editable-alert').remove();

							mesour.popover.show(field.getField(), function() {
								popover.prepend(alertElement);
							});
							input = popover.find('[name="' + data.error.field + '"]');
						} else {
							if (!_this.isDisabledInlineAlerts()) {
								field.getField().find('.mesour-editable-alert').remove();
								field.getField().prepend(alertElement);
							}
							input = field.getField().find('[name="' + data.error.field + '"]');
						}
						if (data.error.field) {

							input.closest('.input-group').addClass('has-error');
							input.trigger('focus');
						}
					} else {
						form.find('.mesour-editable-alert').remove();
						form.prepend(_this.createAlert(data.error.message));
						if (data.error.field) {
							var input = form.find('[name="' + data.error.field + '"]');
							input.closest('.form-group').addClass('has-error');
							input.trigger('focus');
						}
					}
				}
			} catch (e) {
				if (response.status === 200) {
					if (field) {
						field.saveValue();
						removeEditedField(fieldName, identifier);
					}
					mesour.core.redrawCallback(response);

					if (!isNormal) {
						mesour.editable.removeReference(name, table);
						modal.getModalBody().empty().append(
							_this.createAlert(editableWidget.getTranslate('dataSaved'), 'success', false)
						);
						modal.disable();
					}

					onComplete(fieldName);
				} else {
					var message = editableWidget.getTranslate('statusError').replace('%status%', response.status);
					if (isNormal) {
						alert(message);
					} else {
						form.find('.mesour-editable-alert').remove();
						form.prepend(
							_this.createAlert(
								message,
								'danger'
							)
						);
					}
				}
			}
		};

		this.remove = function(fieldName, element, identifier, value) {
			var created = mesour.core.createLink(name, 'remove', postData = {
				name: fieldName,
				identifier: identifier,
				value: value
			}, true);

			$.post(created[0], created[1]).complete(function(r) {
				postCallback(null, fieldName, null, null, r, identifier, true);
			});
			editableWidget.removeReference(name);
		};

		this.newEntry = function(fieldName, element, identifier) {
			if (!element) {
				throw new Error('Element for edit is required.');
			}
			var fieldStructure = getFieldStructure(fieldName);

			editField(fieldStructure, element, identifier);
		};

		this.edit = function(fieldName, element, identifier, value, forceForm) {
			if (!element) {
				throw new Error('Element for edit is required.');
			}
			var fieldStructure = getFieldStructure(fieldName);

			var edited = getEditedField(fieldName, identifier, false);

			editField(fieldStructure, element, identifier, value, forceForm);
		};

		this.save = function(fieldName, identifier) {
			var field = getEditedField(fieldName, identifier);

			var values = field.getValues(),
				postData = {
					name: fieldName,
					identifier: identifier,
					params: values['params'],
					newValue: values['value'],
					oldValue: values['oldValue']
				};

			var created = mesour.core.createLink(name, 'edit', postData, true);

			$.post(created[0], created[1]).complete(function(r) {
				postCallback(null, fieldName, null, field, r, identifier, true);
			});
		};

		this.editForm = function(fieldName, identifier, form, table) {
			var field = getEditedField(fieldName, identifier);

			var values = field.getValues(),
				postData = {
					name: fieldName,
					identifier: identifier,
					params: values['params'],
					values: values['newValues'],
					oldValues: values['oldValues']
				};
			if (values['reference']) {
				postData['reference'] = values['reference'];
			}

			var created = mesour.core.createLink(name, 'editForm', postData, true);
			$.post(created[0], created[1]).complete(function(r) {
				postCallback(form, fieldName, table, field, r, identifier);
			});
		};

		this.attach = function(fieldName, identifier, form, table) {
			var field = getEditedField(fieldName, identifier);

			var values = field.getValues(),
				postData = {
					name: fieldName,
					identifier: identifier,
					params: values['params'],
					reference: values['reference']
				};

			var created = mesour.core.createLink(name, 'attach', postData, true);
			$.post(created[0], created[1]).complete(function(r) {
				postCallback(form, fieldName, table, field, r, identifier);
			});
		};

		this.create = function(fieldName, identifier, form, table) {
			var field = getEditedField(fieldName, identifier);

			var values = field.getValues(),
				postData = {
					name: fieldName,
					params: values['params'],
					identifier: identifier,
					references: values['values'],
					values: _this.getModal().getFormValues(form)
				};
			var created = mesour.core.createLink(name, 'create', postData, true);
			$.post(created[0], created[1]).complete(function(r) {
				postCallback(form, fieldName, table, field, r);
			});
		};

	};

})(jQuery);