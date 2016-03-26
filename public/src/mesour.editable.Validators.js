/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;

(function ($) {

    mesour._editable.Validators = {
        validateNumber: function (editable, val, input, isNormal) {
            if (!mesour._editable.isNumeric(val.replace(',', '.'))) {
                if (!isNormal) {
                    input.closest('form')
                        .find('.main-form-group:visible:first')
                        .prepend(
                            editable.createAlert(editable.getEditableWidget().getTranslate('invalidNumber'), 'danger')
                        );
                }
                if (input) {
                    input.closest('.form-group').addClass('has-error');
                    input.trigger('focus');
                }
                return false;
            }
            return true;
        }
    };

})(jQuery);