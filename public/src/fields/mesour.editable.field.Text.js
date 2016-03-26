/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function ($) {

    mesour._editable.fields.Text = function (fieldStructure, editable, element, parameters, identifier, isNumber) {

        this.TYPE = editable.TYPE_TEXT;

        parameters = parameters || {};

        var _this = this,
            fieldName = fieldStructure['name'],
            oldValue = $.trim(element.text()),
            input = $('<input type="text" value="' + oldValue + '" class="form-control">'),
            reset = function () {
                element.empty().text(oldValue);
            };

        input.on('keydown.mesour-editable', function (e) {
            if (e.keyCode === 13) {
                editable.save(fieldName, identifier);
            } else if (e.keyCode === 27) {
                reset();
            }
        });

        input.css('width', '100%');

        element.empty().append(input);

        input.focus();

        this.getInput = function () {
            return input;
        };

        this.getValue = function () {
            return {
                oldValue: oldValue,
                value: input.val(),
                params: parameters
            };
        };

        this.reset = function () {
            reset();
        };

        this.save = function () {
            element.empty().text(input.val());
        };

    };

})(jQuery);