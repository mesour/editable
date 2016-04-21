/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;

(function($) {

	mesour._editable.Validators = {
		validateNumber: function(editable, val, input, isNormal, isNullable, field) {
			var isNumeric = mesour._editable.isNumeric(val.replace(',', '.'));
			if ((!isNullable && !val) || (isNullable && val && !isNumeric)) {
				if (!isNormal) {
					input.closest('form')
						.find('.main-form-group:visible:first')
						.prepend(
							editable.createAlert(editable.getEditableWidget().getTranslate('invalidNumber'), 'danger')
						);
				} else {
					var popover = $('.editable-popover:visible .popover-content');
					popover.find('.mesour-editable-alert').remove();

					mesour.popover.show(field.getElement(), function() {
						popover.prepend(
							editable.createAlert(editable.getEditableWidget().getTranslate('invalidNumber'), 'danger', false)
						);
					});
				}

				if (input) {
					var group = input.closest('.form-group');
					if (!group.is('*')) {
						group = input.closest('.input-group');
					}
					group.addClass('has-error');
					input.trigger('focus');
				}
				return false;
			}
			return true;
		}
	};

})(jQuery);