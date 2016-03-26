/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;
mesour._editable.fields = !mesour._editable.fields ? {} : mesour._editable.fields;

mesour._editable.numberFormat = function (number, decimals, dec_point, thousands_sep) {
    number = (number + '')
        .replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + (Math.round(n * k) / k)
                    .toFixed(prec);
        };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
        .split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '')
            .length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1)
            .join('0');
    }
    return s.join(dec);
};
mesour._editable.isNumeric = function (mixed_var) {
    var whitespace =
        " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
    return (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -
            1)) && mixed_var !== '' && !isNaN(mixed_var);
};

(function ($) {

    var isNumeric = mesour._editable.isNumeric;

    var numberFormat = mesour._editable.numberFormat;

    mesour._editable.fields.Number = function (fieldStructure, editable, element, parameters, identifier) {

        this.TYPE = editable.TYPE_NUMBER;

        parameters = parameters || {};

        var text = new mesour._editable.fields.Text(fieldStructure, editable, element, parameters, identifier);

        var _this = this,
            fieldName = fieldStructure['name'],
            unit = fieldStructure['unit'],
            separator = fieldStructure['separator'],
            decimalPoint = fieldStructure['decimalPoint'],
            decimals = fieldStructure['decimals'],
            input = text.getInput();

        function fixNumber(value) {
            return $.trim(value.replace(new RegExp(separator === '.' ? '\\.' : separator, 'g'), '')
                .replace(decimalPoint, '.')
                .replace(unit, ''));
        };

        input.val(fixNumber(input.val()));
        input.attr('placeholder', mesour._editable.numberFormat(0, decimals));

        if (unit) {
            var inputGroup = $('<div class="input-group">');
            input.wrap(inputGroup);

            var formGroup = $('<div class="form-group">');
            input.closest('.input-group').wrap(formGroup);

            input.after('<span style="" class="input-group-addon">' + unit + '</span>');
        }

        input.focus();

        input.off('keydown.mesour-editable');
        input.on('keydown.mesour-editable', function (e) {
            if (e.keyCode === 13) {
                var isValid = mesour._editable.Validators.validateNumber(editable, input.val(), input, true);
                if (isValid) {
                    editable.save(fieldName, identifier);
                }
            } else if (e.keyCode === 27) {
                text.reset();
            }
        });

        this.getValue = function () {
            var value = text.getValue();

            value['oldValue'] = fixNumber(value['oldValue']);

            return value;
        };

        this.reset = function () {
            text.reset();
        };

        this.save = function () {
            var val = input.val();
            element.empty().text(numberFormat(val, decimals, decimalPoint, separator) + (unit ? (' ' + unit) : ''));
        };

    };

})(jQuery);