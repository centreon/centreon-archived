/**
 * Created by rridene on 18/05/2015.
 */

/*-- Float label --*/
$(document).ready(function() {

    // Test for placeholder support
    $.support.placeholder = (function () {
        var i = document.createElement('input');
        return 'placeholder' in i;
    })();

    // Hide labels by default if placeholders are supported


    if($.support.placeholder) {

        if($('input').val() == 0) {
            $('.CentreonForm .form-group').each(function(){
                $(this).addClass('js-hide-label');
                $('input').css({'padding':'10px 12px 12px'});
            });
        }

        // Code for adding/removing classes here

        $('.CentreonForm .form-group').find('input, textarea').on('keyup blur focus', function(e){

            // Cache our selectors
            var $this = $(this),
                $label = $this.prev('label'),
                $parent = $this.parent();

                    if (e.type == 'keyup') {
                        if( $this.val() == '' ) {
                            $parent.addClass('js-hide-label');
                        } else {
                            $parent.removeClass('js-hide-label');
                        }
                    }

                    else if (e.type == 'blur') {
                        $this.next().css({'display':'none'});

                        if( $this.val() == '' ) {
                            $parent.addClass('js-hide-label');
                            $this.css({'padding':'10px 12px 12px'});
                        }
                        else {
                            $parent.removeClass('js-hide-label').addClass('js-unhighlight-label');
                        }
                    }
                    else if (e.type == 'focus') {

                        $this.next().css({'display': 'block'});
                        $this.css({'padding':'16px 12px 6px'});
                        $parent.removeClass('js-hide-label').addClass('js-unhighlight-label');

                        if ($this.val() == '') {
                            $parent.addClass('js-hide-label').addClass('js-unhighlight-label');
                            $this.css({'padding':'10px 12px 6px'});
                        }
                    }
            })
    }

});