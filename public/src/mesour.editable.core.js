/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour.editable = !mesour.editable ? {} : mesour.editable;

(function ($) {

    var Editable = function (options) {

        var _this = this;

        this.items = {};

        var references = {};

        var traslations = {
            select: 'Select...',
            selectOne: 'Select one',
            selectExisting: 'Select from existing',
            allSelected: 'All existing companies are attachet to this client...',
            attachExisting: 'Attach existing',
            createNew: 'Create new',
            reset: 'Reset value',
            emptyButton: 'Set empty value',
            saveItem: 'Save',
            editItem: 'Edit in form',
            cancelEdit: 'Cancel',
            dataSaved: 'Successfuly saved',
            saveEmptyValue: 'Really save empty value?',
            emptyValue: '- none',
            invalidNumber: 'Value must be valid number',
            statusError: 'ERROR! Status: %status%. Try save data later.'
        };

        this.enable = function (name, isInline, isDisabledInlineAlerts) {
            if (_this.items[name]) {
                _this.items[name].refresh();
                return;
            }
            $.get(mesour.core.createLink(name, 'dataStructure')).complete(function (r) {
                try {
                    var data = $.parseJSON(r.responseText).data;

                    _this.items[name] = new mesour._editable.Editable(name, data, _this);
                    _this.items[name].setInline(isInline);
                    _this.items[name].setDisabledInlineAlerts(isDisabledInlineAlerts);
                } catch (e) {
                    throw e;
                }
            });
        };

        this.getTranslate = function (key) {
            return traslations[key];
        };

        this.setTranslations = function (translates) {
            traslations = translates;
        };

        this.removeReference = function (name, table) {
            if (table) {
                delete references[name][table];
            } else {
                delete references[name];
            }
        };

        this.getReferenceData = function (name, table, callback, referencedTable) {
            references[name] = references[name] || {};
            if (!references[name][table]) {
                var postData = {
                    'table': table,
                    'referencedTable': null
                };
                if (referencedTable) {
                    postData['referencedTable'] = referencedTable;
                }
                var created = mesour.core.createLink(name, 'referenceData', postData, true);
                $.post(created[0], created[1]).complete(function (r) {
                    var data = $.parseJSON(r.responseText);
                    references[name][table] = data;

                    callback(data);
                    try {

                    } catch (e) {
                        mesour.core.redrawCallback(r);
                    }
                });
            } else {
                callback(references[name][table]);
            }
        };

        this.getComponent = function (name) {
            if (!_this.items[name]) {
                throw new Error('Editable component with name ' + name + ' not exits');
            }
            return _this.items[name];
        };

    };

    mesour.core.createWidget('editable', new Editable());
})(jQuery);