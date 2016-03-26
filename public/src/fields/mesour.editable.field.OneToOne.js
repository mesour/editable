/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function ($) {

    mesour._editable.fields.OneToOne = function (fieldStructure, editable, element, parameters, identifier, value) {

        this.TYPE = editable.TYPE_ONE_TO_ONE;

        parameters = parameters || {};

        var _this = this,
            select,
            oldValue = element.text(),
            title = fieldStructure['title'],
            reference = fieldStructure['reference'],
            table = reference['table'],
            primaryKey = reference['primary_key'],
            column = reference['column'],
            fieldName = fieldStructure['name'],
            createNewRow = parameters['create_new_row'];

        editable.getEditableWidget().getReferenceData(editable.getName(), table, function (data) {
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
                    name: data[i][column]
                };
            }

            select = new mesour._editable.fields.Enum(fieldStructure, editable, element, parameters, identifier, value, values);

            select.getSelect().off('change.mesour-editable');
            select.getSelect().on('change.mesour-editable', function () {
                var $this = $(this);

                if ($this.val() === '__add_new_record__') {
                    editable.getModal().show();
                    editable.getModal().setTitle(title);

                    var elementFields = editable.getElementStructure(table)
                    var form = editable.getModal().createForm(elementFields.fields ? elementFields.fields : elementFields);
                    editable.getModal().addHiddenField(form, '__fieldName', fieldName);
                    editable.getModal().addHiddenField(form, '__identifier', identifier);
                    editable.getModal().onSubmit(identifier, function (e, currentIdentifier) {
                        _this.getValue = function () {
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

                        editable.create(fieldName, currentIdentifier, form, table);

                        _this.getValue = function () {
                            return select.getValue();
                        };
                    })
                } else {
                    editable.save(fieldName, identifier);
                }
            });
        });

        this.getValue = function () {
            return select.getValue();
        };

        this.reset = function () {
            select.reset();
        };

        this.save = function () {
            select.save();
        };

    };

})(jQuery);