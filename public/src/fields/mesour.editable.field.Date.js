/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function ($) {

    mesour._editable.fields.Date = function (fieldStructure, editable, element, parameters, identifier) {

        this.TYPE = editable.TYPE_DATE;

        parameters = parameters || {};

        var text = new mesour._editable.fields.Text(fieldStructure, editable, element, parameters, identifier);

        var _this = this,
            fieldName = fieldStructure['name'],
            format = fieldStructure['format'],
            input = text.getInput();

        element.parent().css('position', 'relative');

        var oldValue = input.val();
        input.val(!oldValue || oldValue === '-' ? '' : oldValue);
        mesour.dateTimePicker.create(input, format, true);
        mesour.dateTimePicker.show(input);

        this.getValue = function () {
            return text.getValue();
        };

        this.reset = function () {
            mesour.dateTimePicker.destroy(input);
            text.reset();
        };

        this.save = function () {
            mesour.dateTimePicker.destroy(input);
            text.save();
        };

    };

})(jQuery);