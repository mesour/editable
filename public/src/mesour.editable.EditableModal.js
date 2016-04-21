/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;

(function($) {

	mesour._editable.EditableModal = function(name, editable) {

		var _this = this,
			element = $('[data-editable-modal="' + name + '"]'),
			modalName = element.attr('data-mesour-modal');

		function toogle() {
			var $this = $(this),
				group = $this.attr('data-mesour-toggle'),
				others = $this.closest('[data-mesour-toggle-group]')
					.find('[data-mesour-toggle]')
					.not($this);

			element.find('.has-error').removeClass('has-error');
			element.find('.alert').remove();

			if (!$this.hasClass('active')) {
				others.removeClass('active');
				$this.addClass('active');

				element.find('input[name="__type_selector"]').val(group);
				element.find('[data-mesour-has-toggle]').hide();
				element.find('[data-mesour-has-toggle="' + group + '"]').show();
			}
		};

		function addTextField(group, id, name, key, placeholder) {
			var label = $('<label for="' + id + '">' + name + '</label>');
			group.append(label);

			var textField = $('<input type="text" class="form-control" id="' + id + '" name="' + key + '">');
			if (placeholder) {
				textField.attr('placeholder', placeholder);
			}
			group.append(textField);

			textField.on('keydown', function(e) {
				if (e.keyCode === 13) {
					e.preventDefault();
					var formSave = element.find('[data-editable-form-save]');
					formSave.trigger('click');
				}
			});

			return textField;
		};

		function addTextarea(group, id, name, key, placeholder) {
			var label = $('<label for="' + id + '">' + name + '</label>');
			group.append(label);

			var textField = $('<textarea type="text" class="form-control" id="' + id + '" name="' + key + '">');
			if (placeholder) {
				textField.attr('placeholder', placeholder);
			}
			group.append(textField);

			textField.on('keydown', editable.textareaTabFix);

			return textField;
		};

		function addCheckboxField(group, id, name, key) {
			var label = $('<label for="' + id + '"> ' + name + '</label>');
			group.append(label);

			var checkbox = $('<input type="checkbox" id="' + id + '" name="' + key + '">');
			label.prepend(checkbox);
			group.append(label);

			return checkbox;
		};

		function addGroupToForm(form, className) {
			className = !className ? 'form-group' : className;
			var group = $('<div class="' + className + '">');
			form.append(group);
			return group;
		};

		function createTogglesGroup() {
			var group = $('<div class="main-form-group">');
			var formGroup = addGroupToForm(group);

			formGroup.append('<label for="__type_selector">' + editable.getEditableWidget().getTranslate('selectOne') + '</label>');

			var groupHtml = $('<div class="btn-group" role="group" aria-label="__type_selector" data-mesour-toggle-group="true">');
			var oldButton = $('<button type="button" class="btn btn-info" data-mesour-toggle="old">' + editable.getEditableWidget().getTranslate('attachExisting') + '</button>');
			var newButton = $('<button type="button" class="btn btn-info" data-mesour-toggle="new">' + editable.getEditableWidget().getTranslate('createNew') + '</button>');

			oldButton.on('click.mesour-editable-toggle', toogle);
			newButton.on('click.mesour-editable-toggle', toogle);

			groupHtml.append(oldButton);
			groupHtml.append(newButton);

			oldButton.trigger('click.mesour-editable-toggle');

			group.append($(groupHtml));
			return group;
		};

		function createForm(dataStructure) {
			var form = $('<form><div class="main-form-group" data-mesour-has-toggle="new">');

			function addFormGroup(className) {
				return addGroupToForm(form.children('div[data-mesour-has-toggle="new"]'), className);
			};

			for (var i in dataStructure) {
				if (!dataStructure.hasOwnProperty(i)) {
					continue;
				}
				var structure = dataStructure[i];

				if (structure['type'] === editable.TYPE_TEXT) {
					var field,
						args = [addFormGroup(), name + structure['name'], structure['title'], structure['name'], structure['title']];
					if (structure['hasTextarea'] === 'false') {
						field = addTextField.apply(_this, args);
					} else if (structure['hasTextarea'] === 'true') {
						field = addTextarea.apply(_this, args);
					}
				} else if (structure['type'] === editable.TYPE_BOOL) {
					var field = addCheckboxField(addFormGroup('checkbox'), name + structure['name'], structure['title'], structure['name']);
				} else if (structure['type'] === editable.TYPE_NUMBER) {
					var field = addTextField(
						addFormGroup(),
						name + structure['name'],
						structure['title'],
						structure['name'],
						mesour._editable.numberFormat(0, structure['decimals'])
					);
					field.attr('data-validate-number', 'true');
					field.attr('data-nullable', structure['nullable'] ? 'true' : 'false');
					if (structure['unit']) {
						field.wrap('<div class="input-group">');
						field.after($('<span style="text-align:right;"  class="input-group-addon">' + structure['unit'] + '</span>'));
					}
				} else if (structure['type'] === editable.TYPE_ENUM) {
					var group = addFormGroup(),
						id = name + structure['name'];
					var label = $('<label for="' + id + '">' + structure['title'] + '</label>');
					group.append(label);
					var select = $('<select class="form-control" id="' + id + '" placeholder="' + structure['title'] + '" name="' + structure['name'] + '">');
					if (structure['nullable']) {
						select.prepend('<option value="">' + editable.getEditableWidget().getTranslate('emptyValue') + '</option>');
					}
					for (var j in structure['values']) {
						if (!structure['values'].hasOwnProperty(j)) {
							continue;
						}
						var current = structure['values'][j];
						select.append('<option value="' + current['key'] + '">' + current['name'] + '</option>');
					}
					group.append(select);
				} else if (structure['type'] === editable.TYPE_DATE) {
					var field = addTextField(addFormGroup(), name + structure['name'], structure['title'], structure['name'], structure['format']);
					mesour.dateTimePicker.create(field, structure['format']);
				}
			}

			return form;
		};

		this.getName = function() {
			return modalName;
		};

		this.show = function() {
			mesour.modal.show(modalName);
			mesour.modal.onHide(modalName, function() {
				var _form = element.find('form');
				editable.close(_form.find('[name="__fieldName"]').val(), _form.find('[name="__identifier"]').val());
			});
		};

		this.setTitle = function(title) {
			var modalHeader = element.find('.modal-header'),
				titleSpan = modalHeader.find('.modal-title');

			if (!titleSpan.is('*')) {
				titleSpan = $('<h4 class="modal-title">');
				modalHeader.append(titleSpan);
			}

			titleSpan.empty().text(title);
		};

		this.hide = function() {
			mesour.modal.hide(modalName);
		};

		this.onSubmit = function(identifier, callback) {
			var refreshCallback = function(e) {
				e.preventDefault();

				var _form = element.find('form'),
					values = _this.getFormValues(_form);

				if (identifier != _form.find('[name="__identifier"]').val()) {
					return;
				}

				_form.find('.has-error').removeClass('has-error');
				_form.find('.alert').remove();

				var valid = true;
				for (var i in values) {
					if (!values.hasOwnProperty(i)) {
						continue;
					}
					var input = _form.find('[name="' + i + '"]');

					if (
						input.attr('data-validate-number') && input.closest('.main-form-group').is(':visible')
						&& !mesour._editable.Validators.validateNumber(
							editable,
							values[i],
							input,
							false,
							input.attr('data-nullable') === 'true' ? true : false
						)
					) {
						valid = false;
					}
				}

				if (valid) {
					callback(e, _form.find('[name="__identifier"]').val());
				}
			};

			_this.refresh = function() {
				var formSave = element.find('[data-editable-form-save]');
				formSave.off('click.mesour-editable');
				formSave.on('click.mesour-editable', refreshCallback);
			};
			_this.refresh();
		};

		this.getModalBody = function() {
			return mesour.modal.getBody(modalName);
		};

		this.disable = function() {
			element.find('[data-editable-form-save]').hide();
		};

		this.enable = function() {
			element.find('[data-editable-form-save]').show();
		};

		this.createForm = function(dataStructure) {
			var body = _this.getModalBody(),
				form = createForm(dataStructure);
			body.empty();
			body.append(form);
			return form;
		};

		this.appendToggleForm = function(form, name) {
			_this.addHiddenField(form, '__type_selector', name);

			var group = $('<div class="main-form-group" data-mesour-has-toggle="' + name + '">');
			form.append(group);
			form.prepend(createTogglesGroup());
			return group;
		};

		this.addHiddenField = function(form, name, value, inData) {
			var input = form.find('input[name="' + name + '"]');
			if (!input.is('*')) {
				input = $('<input type="hidden" name="' + name + '" value="' + value + '">');
				form.append(input);
			} else {
				input.val(value);
			}
			if (inData) {
				input.attr('data-editable-in-data', true);
			}
			return input;
		};

		this.getFormValues = function(form) {
			var values = {};
			form.find('input[type=text], select, [data-editable-in-data], textarea').each(function() {
				var $this = $(this);
				values[$this.attr('name')] = $this.val();
			});
			form.find('input[type=checkbox]').each(function() {
				var $this = $(this);
				values[$this.attr('name')] = $this.is(':checked') ? true : false;
			});
			return values;
		};

		this.fillForm = function(form, values, hidden) {
			for (var _key in values) {
				if (!values.hasOwnProperty(_key)) {
					continue;
				}
				var input = form.find('input[name="' + _key + '"], select[name="' + _key + '"], textarea[name="' + _key + '"]');
				if (input.is('*')) {
					if (input.is('input[type=checkbox]') && values[_key]) {
						input.prop('checked', true);
					} else {
						input.val(values[_key]);
					}
				}
			}
			for (var _key in hidden) {
				if (!hidden.hasOwnProperty(_key)) {
					continue;
				}
				var hiddenField = _this.addHiddenField(form, _key, hidden[_key]);
				hiddenField.attr('data-editable-in-data', 'true');
			}
		};

	};

})(jQuery);