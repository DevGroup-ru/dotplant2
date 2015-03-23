
function getUrl(url) {
    var match = url.match(/([^\?]*)/i);
    url = match[1];
    var query_params_match = url.match(/\?(.*)$/),
        query_params = "";
    if (query_params_match !== null) {
        query_params = '&' + query_params_match[1];
    }

    var params = {};
    $('.filter-dynamic .filter-form [name]').each(function(index) {
        var $this = $(this);
        if ($this.val() != "") {
            params[$this.attr("name")] = $this.val();
        } else {
            delete params[$this.attr("name")];
        }
    });
    var query = "";
    if (Object.keys(params).length > 0) {
        query += '?' + $.param(params);
    } else if (query_params.length > 0) {
        query_params = '?' + query_params;
    }

    return url + query + query_params;
}

jQuery.fn.getFilters = function() {
    $(this).on('click', 'a', function() {

        location.href = getUrl($(this).attr('href'));

        return false;
    });
    $(this).find('.filter-form').on('focusout', 'input', function() {
        location.href = getUrl(location.href);
    });
};