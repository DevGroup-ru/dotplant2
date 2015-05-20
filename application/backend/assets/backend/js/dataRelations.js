var comparisonData = {
    widgetId: '',
    options: {},
    data: {},
    keys: {
        'field': 'fields',
        'property': 'properties',
        'relation': 'relations'
    },
    init: function () {
        self = this;
        $('#' + this.widgetId + ' .select-list').each(function (index) {
            $(this).change(function () {

                self.displayRow(this);
            });
        });

        this.loadData();

    },

    loadData: function () {
        for (var key in this.data) {
            element = $('select[name="data[' + key + '][type]"]');
            element
                .find("[value='" + this.data[key]['type'] + "']")
                .attr('selected', 'selected');

            this.displayRow(element);
            if (this.data[key]['type'] == 'field' || this.data[key]['type'] == 'property') {

                $('select[name="data[' + key + '][key]"]')
                    .find("[value='" + this.data[key]['key'] + "']")
                    .attr('selected', 'selected');
            } else if (this.data[key]['type'] == 'relation') {


                $('select[name="data[' + key + '][relationName]"]')
                    .find("[value='" + this.data[key]['relationName'] + "']")
                    .attr('selected', 'selected');
                $('#' + self.widgetId + ' #data-' + dataKey + ' .relation-options')
                    .replaceWith(self.createInfoRelation(this.data[key]['type'], key));

                $('select[name="data[' + key + '][key]"]')
                    .find("[value='" + this.data[key]['key'] + "']")
                    .attr('selected', 'selected');


            }
        }
    },
    displayRow: function (element) {
        key = $(':selected', element).val();
        dataKey = $(element).attr('data-key');
        if (key == 'field' || key == 'property') {
            $('#' + this.widgetId + ' #data-' + dataKey + ' td').last().html(this.renderOptions(key, dataKey));
        } else if (key == 'relation') {

            $('#' + this.widgetId + ' #data-' + dataKey + ' td').last()
                .html('<div class="form-inline">')
                .append(this.createDropDownRelation(key, dataKey))
                .append(this.createInfoRelation(key, dataKey))
                .append('</div>');

            self = this;
            $('#' + this.widgetId + ' #data-' + dataKey + ' [name ="data[' + dataKey + '][relationName]"]').change(function () {
                $('#' + self.widgetId + ' #data-' + dataKey + ' .relation-options').replaceWith(self.createInfoRelation(key, dataKey));
            });

        } else {
            $('#' + this.widgetId + ' #data-' + dataKey + ' td').last().html('');
        }
    },
    renderOptions: function (key, dataKey) {
        data = this.options[this.keys[key]];
        select = '<div class="form-group"><select name="data[' + dataKey + '][key]" class="form-control">';
        for (var k in data) {
            select += '<option value="' + k + '">' + data[k] + '</option>';
        }
        select += '</select></div>';
        return select;
    },
    createDropDownRelation: function (key, dataKey) {
        data = this.options[this.keys[key]];
        result = '<div class="form-group "><select name="data[' + dataKey + '][relationName]" class="form-control">';
        for (var k in data) {
            result += '<option value="' + k + '">' + data[k]['relationName'] + '</option>';
        }
        result += '</select></div>';
        return result;
    },
    createInfoRelation: function (key, dataKey) {
        selectReletion = $('#' + this.widgetId + ' #data-' + dataKey + ' [name ="data[' + dataKey + '][relationName]"] :selected').val();
        data = this.options[this.keys[key]][selectReletion]['values'];
        result = '<div class="form-group relation-options"><select name="data[' + dataKey + '][key]" class="form-control">';
        for (var k in data) {
            result += '<option value="' + k + '">' + data[k] + '</option></div>';
        }
        result += '</select>'

        result += '<input value="' + this.options[this.keys[key]][selectReletion]['class'] + '" type="hidden"  name="data[' + dataKey + '][class]" >';
        return result;
    }
};