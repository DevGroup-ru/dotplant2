(function( $ ){

    var methods = {
        init : function( options ) { 

            var settings = $.extend( {
                // Block with loader css class
                'block_class': 'block-with-loader',

                // Loader css class
                'loader_class': 'block-loader',

                // Loader template
                'template': '<div class="block-loader"><i class="fa fa-spinner fa-spin"></i></div>'
            }, options);

            return this.each(function(){

                var $this = $(this),
                    data = $this.data('blockLoader')

                if ( ! data ) {

                    var $loader = $(settings.template).appendTo($this);
                    $this.addClass('block-with-loader');
                    $this.data('blockLoader', {
                        'loader': $loader
                    });

                }
            })

        },
        destroy : function( ) {
            return this.each(function(){

                var $this = $(this),
                    data = $this.data('blockLoader');

                $this.removeData('blockLoader');

            })
        },
        show : function ( ) {
            /**
             * Shows loader
             */

            return this.each(function() {
                var $this = $(this),
                    data = $this.data('blockLoader');
                if ( ! data ) {
                    $this.blockLoader('init');
                    data = $this.data('blockLoader');
                }
                data.loader.show().css('display', 'table-cell');
            });
        },
        hide : function ( ) {
            /**
             * Hides loader
             */

            return this.each(function() {
                var $this = $(this),
                    data = $this.data('blockLoader');
                if ( ! data ) {
                    $this.blockLoader('init');
                    data = $this.data('blockLoader');
                }
                data.loader.hide();
            });
        }
    };

    $.fn.blockLoader = function( method ) {
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method with name ' +  method + ' does not exist for plugin jQuery.blockLoader' );
        } 
    };

})( jQuery );
