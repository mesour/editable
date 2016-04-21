/**
 * @author Matous Nemec (http://mesour.com)
 */
var mesour = !mesour ? {} : mesour;
mesour._editable = !mesour._editable ? {} : mesour._editable;

(function($) {

	mesour._editable.FieldEditor = function(editable, fieldStructure, element, identifier, value, forceForm) {
		var name = fieldStructure['name'],
			type = fieldStructure['type'],
			parameters = fieldStructure['params'],
			field;

		if (identifier) {
			parameters = parameters[identifier];
		}

		if (type === editable.TYPE_TEXT) {
			field = new mesour._editable.fields.Text(fieldStructure, editable, element, parameters, identifier);
		} else if (type === editable.TYPE_NUMBER) {
			field = new mesour._editable.fields.Number(fieldStructure, editable, element, parameters, identifier);
		} else if (type === editable.TYPE_DATE) {
			field = new mesour._editable.fields.Date(fieldStructure, editable, element, parameters, identifier, value);
		} else if (type === editable.TYPE_ENUM) {
			field = new mesour._editable.fields.Enum(fieldStructure, editable, element, parameters, identifier, value);
		} else if (type === editable.TYPE_BOOL) {
			field = new mesour._editable.fields.Bool(fieldStructure, editable, element, parameters, identifier, value);
		} else if (type === editable.TYPE_ONE_TO_ONE) {
			field = new mesour._editable.fields.OneToOne(fieldStructure, editable, element, parameters, identifier, value);
		} else if (type === editable.TYPE_MANY_TO_ONE) {
			field = new mesour._editable.fields.ManyToOne(fieldStructure, editable, element, parameters, identifier, value, forceForm);
		} else if (type === editable.TYPE_ONE_TO_MANY) {
			field = new mesour._editable.fields.OneToMany(fieldStructure, editable, element, parameters, identifier, value);
		} else if (type === editable.TYPE_MANY_TO_MANY) {
			field = new mesour._editable.fields.ManyToMany(fieldStructure, editable, element, parameters, identifier, value);
		} else {
			throw new Error('Unknown field type ' + type);
		}

		this.getField = function() {
			return field.getElement();
		};

		this.getValues = function() {
			return field.getValue();
		};

		this.resetValue = function() {
			return field.reset();
		};

		this.saveValue = function() {
			return field.save();
		};

	};

})(jQuery);