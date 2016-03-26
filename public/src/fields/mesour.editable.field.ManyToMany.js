/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

(function ($) {

    mesour._editable.fields.ManyToMany = function (fieldStructure, editable, element, parameters, identifier, value) {

        this.TYPE = editable.TYPE_MANY_TO_MANY;

        parameters = parameters || {};

        var _this = this,
            select,
            oldText = element.text(),
            oldValue = element.attr('data-value'),
            title = fieldStructure['title'],
            reference = fieldStructure['reference'],
            table = reference['table'],
            referencedTable = reference['referenced_table'],
            primaryKey = reference['primary_key'],
            column = reference['column'],
            selfColumn = reference['self_column'],
            pattern = reference['pattern'],
            fieldName = fieldStructure['name'],
            createNewRow = parameters['create_new_row'];

        function getDefaultValues(oldValues, values, selfValue, referenceValue, attach) {
            values = !values ? {} : values;
            for (var j in values) {
                if (!values.hasOwnProperty(j)) {
                    continue;
                }
                values[j] = typeof values[j] === 'null' || typeof values[j] === 'undefinded' ? '' : values[j];
            }
            oldValues = !oldValues ? {} : oldValues;
            var out = {
                reference: {
                    'selfColumn': {
                        name: selfColumn,
                        value: selfValue
                    },
                    'column': {
                        name: column,
                        value: referenceValue
                    }
                },
                params: parameters
            };
            if (attach) {
                return out;
            }

            out['oldValues'] = oldValues;
            out['newValues'] = values;

            return out;
        }

        editable.getEditableWidget().getReferenceData(editable.getName(), table, function (data) {
            var references = data.reference ? data.reference : [];
            data = data.data ? data.data : data;

            var values = {};


            if(value) {
                var foundIds = [];
                for (var j in references) {
                    if (!references.hasOwnProperty(j)) {
                        continue;
                    }

                    if (references[j][column] == identifier) {
                        foundIds.push(references[j][primaryKey]);
                    }
                }
                if (!foundIds.length) {
                    throw new Error('Referenced id not found.');
                }
            }

            var foundId = null;
            if(value) {
                var foundId = value;
            }

            for (var i in data) {
                if (!data.hasOwnProperty(i)) {
                    continue;
                }

                if (data[i][primaryKey] == foundId) {
                    values = data[i];
                    break;
                }
            }

            editable.getModal().show();
            editable.getModal().setTitle(title);

            var elementFields = editable.getElementStructure(table);
            var form = editable.getModal().createForm(elementFields.fields ? elementFields.fields : elementFields);

            if (typeof value === 'undefined') {
                form.find('[data-mesour-has-toggle="new"]').prepend('<h3>' + editable.getEditableWidget().getTranslate('createNew') + '</h3>');
            }

            editable.getModal().fillForm(form, values);
            editable.getModal().addHiddenField(form, '__fieldName', fieldName);
            if (identifier) {
                editable.getModal().addHiddenField(form, '__identifier', identifier);

                if(value) {
                    var identifierField = editable.getModal().addHiddenField(form, primaryKey, value);
                    identifierField.attr('data-editable-in-data', 'true');
                }
            }
            if (typeof value === 'undefined') {
                var group = editable.getModal().appendToggleForm(form, 'old');

                var formGroup = $('<div class="form-group"><h3>' + editable.getEditableWidget().getTranslate('selectExisting') + '</h3></div>');
                var select = $('<select class="form-control" name="' + selfColumn + '">');

                var referencedIds = [];
                for (var j in references) {
                    if (!references.hasOwnProperty(j)) {
                        continue;
                    }
                    if (references[j][column] == identifier) {
                        referencedIds.push(references[j][selfColumn]);
                    }
                }

                var selectData = [];
                for (var i in data) {
                    if (!data.hasOwnProperty(i)) {
                        continue;
                    }

                    if (referencedIds.indexOf(data[i][primaryKey]) === -1) {
                        selectData.push(data[i]);
                    }
                }

                if (referencedIds.length > 0) {
                    select.append('<option value="">' + editable.getEditableWidget().getTranslate('select') + '</option>');

                    for (var k = 0; k < selectData.length; k++) {
                        select.append('<option value="' + selectData[k][primaryKey] + '">' + selectData[k]['name'] + '</option>');
                    }
                } else {
                    select.append('<option>' + editable.getEditableWidget().getTranslate('allSelected') + '</option>');
                }

                formGroup.append(select);

                group.append(formGroup);
            }

            editable.getModal().onSubmit(identifier, function (e, currentIdentifier) {
                var typeSelector = form.find('[name="__type_selector"]');

                _this.getValue = function () {
                    if (typeSelector.is('*') && typeSelector.val() === 'old') {
                        var formValues = editable.getModal().getFormValues(form);
                        var referenceColumnValue = formValues[selfColumn];
                        delete formValues[selfColumn];
                        var out = getDefaultValues(values, formValues, referenceColumnValue, currentIdentifier, true);
                        return out;
                    } else {
                        return getDefaultValues(values, editable.getModal().getFormValues(form), value, currentIdentifier);
                    }
                };

                if (typeSelector.is('*') && typeSelector.val() === 'old') {
                    editable.attach(fieldName, currentIdentifier, form, table);
                } else if (typeof value !== 'undefined') {
                    editable.editForm(fieldName, currentIdentifier, form, table);
                } else {
                    editable.create(fieldName, identifier, form, table);
                }

                _this.getValue = function () {
                    return getDefaultValues(values);
                };
            })
        }, referencedTable);

        this.getValue = function () {
            return getDefaultValues();
        };

        this.reset = function () {

        };

        this.save = function () {

        };

    };

})(jQuery);