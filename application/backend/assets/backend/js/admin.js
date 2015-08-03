/* global $, bootbox */
/* jshint -W097 */

"use strict";

var Admin = function() {

	};

Admin.prototype = {
	constructor: Admin

};

Admin.makeSlug = function (selectorsFrom, selectorTo, callback){
	if (selectorsFrom instanceof Array === false) {
		selectorsFrom = [selectorsFrom];
	}
	var valueFrom = '';
	for (var i=0,ii=selectorsFrom.length; i<ii; i++){
		var val=$(selectorsFrom[i]).val().trim();
		if (val.length){
			valueFrom = val;
			break;
		}
	}
	if (valueFrom.length) {
		$.ajax({
			url: "/backend/dashboard/make-slug",
			type: "GET",
			cache: false,
			dataType: 'json',
			data: {
				'word': valueFrom
			}
		}).done(function(data){
            if (typeof(callback) === 'function') {
                data = callback(data);
            }
            var $field = $(selectorTo);
            if (typeof $field.attr('maxlength') !== typeof undefined) {
                data = data.substr(0, $field.attr('maxlength'));
            }
			$field.val(data);
		}).fail(function(jqXHR, textStatus){
			
			$.pnotify({
			    title: 'Error',
			    text: jqXHR.responseText,
			    type: 'error',
			    history: false
			});
		});
	}
};

Admin.makeKey = function(selectorFrom, selectorTo) {
    Admin.makeSlug(selectorFrom, selectorTo, function(data) {
        return data.replace(new RegExp("[\-]+", "g"), '_');
    });
};

Admin.copyFrom = function(selectorsFrom, selectorTo) {
	if (selectorsFrom instanceof Array === false) {
		selectorsFrom = [selectorsFrom];
	}
	var valueFrom = '';
	for (var i=0,ii=selectorsFrom.length; i<ii; i++){
		var val=$(selectorsFrom[i]).val().trim();
		if (val.length){
			valueFrom = val;
			break;
		}
	}
	if (valueFrom.length) {
		$(selectorTo).val(valueFrom);
	}
};

var AdminMenu = {
    typeArray:['normal', 'hidden-menu', 'minified'],

    init:function(){
        this.changeMenu();
    },
    changeMenu:function() {
        $("#hide-menu a").click(function(){
            $.get('/backend/backend-menu/ajax-toggle', {'status': 'hidden-menu'});
        });
        $("span[data-action='minifyMenu']").click(function(){
            $.get('/backend/backend-menu/ajax-toggle', {'status': 'minified'});
        });
    }
};

$(function () {
    AdminMenu.init();
});

$(function () {
    $('.ajax-notifications').on('click', '[data-type="new-notification"]', function () {
        var $link = $(this);
        $.ajax({
            'data' : {
                'id' : $link.data('id')
            },
            'success' : function (data) {
                $link.parents('.unread').eq(0).removeClass('unread');
                $link.remove();
                var $nc = $('.notifications-count');
                $nc.text($nc.text() - 1);
            },
            'url' : '/backend/dashboard/mark-notification'
        });
        return false;
    }).on('click', '.show-more a', function () {
        var $link = $(this);
        var id = $link.data('id');
        var $list = $link.parents('ul').eq(0);
        $link.parent().remove();
        $.ajax({
            'data' : {
                'id' : id
            },
            'success' : function (data) {
                var $nc = $('.notifications-count');
                $(data).find('li').each(function () {
                    var $this = $(this);
                    if ($list.find('li[data-id=' + $this.data('id') + ']').length === 0) {
                        $this.appendTo($list);
                        if ($this.find('.unread').length === 1) {
                            $nc.text($nc.text() + 1);
                        }
                    }
                });
            },
            'url' : '/backend/dashboard/notifications'
        });
        return false;
    });
});

$(function(){
    $('[data-toggle="popover"]').popover({
        container: 'body'
    });
    $('[data-toggle="tooltip"]').tooltip();
    $('body').on('click', '[data-action="delete"]', function() {
        $('#delete-confirmation').attr('data-url', $(this).attr('href')).attr('data-items', '').modal('show');
        return false;
    });
    $('#delete-confirmation [data-action="confirm"]').click(function() {
        var $modal = $(this).parents('.modal').eq(0);
        var data =  typeof($modal.attr('data-items')) == "string" && $modal.attr('data-items').length > 0
            ? {'items': $modal.attr('data-items').split(',')}
            :{};
        $.ajax({
            'url': $modal.attr('data-url'),
            'type': 'post',
            'data': data,
            'success': function (data) {
                location.reload();
            }
        });
        return true;
    });
    $('body').on('click', '[data-action="delete-category"]', function() {
        $('#delete-category-confirmation').attr('data-url', $(this).attr('href')).attr('data-items', '').modal('show');
        return false;
    });
    $('#delete-category-confirmation [data-action="confirm"]').click(function() {
        var $modal = $(this).parents('.modal').eq(0);
        var data =  typeof($modal.attr('data-items')) == "string" && $modal.attr('data-items').length > 0
            ? {'items': $modal.attr('data-items').split(',')}
            :{};
        $.ajax({
            'url': $modal.attr('data-url') + '&mode=' + $('#delete-mode').val(),
            'type': 'post',
            'data': data,
            'success': function (data) {
                location.reload();
            }
        });
        return true;
    });
    $('body').on('click', 'a[data-action="post"]', function(){
        var that = $(this);

        if (that.hasClass('ladda-button') === false) {
            that.addClass('ladda-button');
            that.wrapInner('<span class="ladda-label"></span>');
            that.attr('data-style', 'expand-right');
            that.data('spinnerColor', '#fff');
        }


        var l = that.ladda();
        l.ladda('start');

        var $form = $('<form>')
                .attr('action', that.attr('href'))
                .attr('method', 'post'),
            $hidden = $('<input type="hidden">')
                .attr('name', $('meta[name="csrf-param"]').attr('content'))
                .attr('value', $('meta[name="csrf-token"]').attr('content'));
        $form.append($hidden);
        $('body').append($form);
        $form.submit();

        return false;
    });

});