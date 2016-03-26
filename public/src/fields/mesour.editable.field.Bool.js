/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function ($) {

    mesour._editable.fields.Bool = function (fieldStructure, editable, element, parameters, identifier, value) {

        this.TYPE = editable.TYPE_TEXT;

        parameters = parameters || {};

        var _this = this,
            fieldName = fieldStructure['name'],
            oldValue = element.text(),
            input = $('<input type="checkbox" value="1">'),
            reset = function () {
                element.empty().text(oldValue);
            };

        input.on('change.mesour-editable', function (e) {
            var $this = $(this);
            editable.save(fieldName, identifier);
        });

        if (value == 1 || value === 'true' || value === true) {
            input.prop('checked', true);
        }

        element.empty().append(input);

        this.getInput = function () {
            return input;
        };

        this.getValue = function () {
            return {
                oldValue: value,
                value: input.is(':checked'),
                params: parameters
            };
        };

        this.reset = function () {
            reset();
        };

        this.save = function () {
            element.empty().text(input.is(':checked') ? 'Yes' : 'No');
        };

    };

})(jQuery);