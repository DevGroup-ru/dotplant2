(function ($) {
    function getMessageCount(url, container, done, fail) {
        var onDone = typeof(done) === 'function' ? done : function(data, container) {return false;};
        var onFail = typeof(fail) === 'function' ? fail : function(container) {return false;};
        $.get(url).done(function(data){
            onDone(data, container);
        }).fail(function(){
            onFail(container);
        });
    }
	$.fn.notification = function (url, done, fail) {
		var container = $(this);

		getMessageCount(url, container, done, fail);
		setInterval(getMessageCount, 15000, url, container, done, fail);
	};
})(window.jQuery);