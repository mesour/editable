/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function ($) {

    mesour._editable.fields.Enum = function (fieldStructure, editable, element, parameters, identifier, value, values) {

        this.TYPE = editable.TYPE_ENUM;

        parameters = parameters || {};

        var _this = this,
            oldValue = element.text(),
            fieldName = fieldStructure['name'],
            values = values ? values : fieldStructure['values'],
            select = $('<select class="form-control"></select>');

        select.css('width', '100%');

        for (var i in values) {
            if (!values.hasOwnProperty(i)) {
                continue;
            }
            var option = $('<option>')
                .attr('value', values[i]['key'])
                .text(values[i]['name']);

            if (values[i]['key'] == value) {
                option.prop('selected', true);
            }

            select.append(option);
        }

        element.empty().append(select);

        select.on('change.mesour-editable', function () {
            editable.save(fieldName, identifier);
        });

        this.getSelect = function () {
            return select;
        };

        this.getValue = function () {
            return {
                oldValue: value,
                value: select.find(':selected').val(),
                params: parameters
            };
        };

        this.reset = function () {
            element.empty().text(oldValue);
        };

        this.save = function () {
            element.empty().text(select.find(':selected').text());
        };

    };

})(jQuery);