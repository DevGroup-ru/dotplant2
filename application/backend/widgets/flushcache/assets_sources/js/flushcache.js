$.fn.flushCache = function(url, done, fail) {
    var onDone = typeof(done) === 'function' ? done : function(data) {return false;};
    var onFail = typeof(fail) === 'function' ? fail : function() {return false;};
    $(this).on('click', function() {
        $.post(url)
            .done(function(data) {
                onDone(data);
            })
            .fail(function() {
                onFail();
            });
        return false;
    });
};
