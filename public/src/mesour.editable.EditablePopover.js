/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;

(function($) {

	mesour._editable.EditablePopover = function(editable, element, input, field, softReset, prependButtons) {

		prependButtons = prependButtons || {};

		var _this = this, onSaveCallback, onResetCallback;

		var content = $('<div class="input-group input-group-sm">');
		content.append(input);

		if(editable.isInline()) {
			content.addClass('editable-inline');
		}

		var buttonGroup = $('<span class="input-group-btn"></span>');

		for (var i in prependButtons) {
			if (!prependButtons.hasOwnProperty(i)) {
				continue;
			}
			buttonGroup.append(prependButtons[i]);
		}
		var saveButton = $('<button class="btn btn-primary" title="' + editable.getEditableWidget().getTranslate('saveItem') + '"><i class="fa fa-check"></i></button>');
		buttonGroup.append(saveButton);

		var resetButton = $('<button class="btn btn-default" title="' + editable.getEditableWidget().getTranslate('cancelEdit') + '"><i class="fa fa-remove"></i></button>');
		buttonGroup.append(resetButton);

		input.after(buttonGroup);

		this.addButton = function(button) {
			buttonGroup.prepend(button);
		};

		if (softReset && !editable.isInline()) {
			var softResetButton = $('<i class="fa fa-times-circle editable-soft-reset" title="' + editable.getEditableWidget().getTranslate('reset') + '"></i>');

			if (!input.val()) {
				softResetButton.hide();
			}

			input.after(softResetButton);

			softResetButton.on('click', function() {
				input.val(null);
				softResetButton.hide();
				if (input.attr('data-is-date') !== 'true') {
					input.focus();
				}
			});

			var updateSoftButton = function(e) {
				if ($(this).val().length > 0) {
					softResetButton.show();
				} else {
					softResetButton.hide();
				}
			};
			input.on('propertychange change click keyup input paste blur', updateSoftButton);
		}

		if(!editable.isInline()) {
			mesour.popover.create(element, {
				content: function() {
					return content;
				},
				html: true,
				container: 'body',
				placement: 'auto',
				trigger: 'manual',
				template: '<div class="popover editable-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"><form class="form-inline"></form></div></div>'
			});

			mesour.popover.show(element, function() {
				if (softReset) {
					softResetButton.css('left', input.width());
				}
			}, true);
		} else {
			var oldContent = element.contents().clone();
			element.empty().append(content);
		}

		this.onSave = function(callback) {
			onSaveCallback = callback;
		};

		this.onReset = function(callback) {
			onResetCallback = callback;
		};

		this.hide = function() {
			if(!editable.isInline()) {
				mesour.popover.hide(element);
			} else {
				element.empty().append(oldContent);
			}
		};

		this.destroy = function() {
			if(!editable.isInline()) {
				mesour.popover.destroy(element);
			} else {
				element.empty().append(oldContent);
			}
		};

		saveButton.on('click', function(e) {
			e.preventDefault();
			if (typeof onSaveCallback === 'function') {
				onSaveCallback.apply(this, [e]);
			}
		});

		resetButton.on('click', function(e) {
			e.preventDefault();
			if (typeof onResetCallback === 'function') {
				onResetCallback.apply(this, [e]);
			}
		});

	};

})(jQuery);