(function($) {
    $.fn.toggleClick = function(){

        var functions = arguments ;

        return this.click(function(){
                var iteration = $(this).data('iteration') || 0;
                functions[iteration].apply(this, arguments);
                iteration = (iteration + 1) % functions.length ;
                $(this).data('iteration', iteration);
        });
    };
})(jQuery);