/**
 * Created by rridene on 31/03/2015.
 */


// Custom scripts
$(document).ready(function () {

    // MetsiMenu
    $('#side-menu').metisMenu();

    // Collapse ibox function
    $('.collapse-link').click( function() {
        var ibox = $(this).closest('div.ibox');
        var button = $(this).find('i');
        var content = ibox.find('div.ibox-content');
        content.slideToggle(200);
        button.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
        ibox.toggleClass('').toggleClass('border-bottom');
        setTimeout(function () {
            ibox.resize();
            ibox.find('[id^=map-]').resize();
        }, 50);
    });

    // Close ibox function
    $('.close-link').click( function() {
        var content = $(this).closest('div.ibox');
        content.remove();
    });

    // Small todo handler
    $('.check-link').click( function(){
        var button = $(this).find('i');
        var label = $(this).next('span');
        button.toggleClass('fa-check-square').toggleClass('fa-square-o');
        label.toggleClass('todo-completed');
        return false;
    });

    // Append config box / Only for demo purpose
   /* $.get("skin-config.html", function (data) {
        $('body').append(data);
    });*/

    // minimalize menu
    $('.navbar-minimalize').click(function () {
        $("body").toggleClass("mini-navbar");
        SmoothlyMenu();
    })

    // tooltips
    $('.tooltip-demo').tooltip({
        selector: "[data-toggle=tooltip]",
        container: "body"
    })

    // Move modal to body
    // Fix Bootstrap backdrop issu with animation.css
    $('.modal').appendTo("body")

    // Full height of sidebar
    function fix_height() {
        var heightWithoutNavbar = $("body > #mainCntr").height() - 61;
        $(".sidebard-panel").css("min-height", heightWithoutNavbar + "px");
    }
    fix_height();

    // Fixed Sidebar
    // unComment this only whe you have a fixed-sidebar
       $(window).bind("load", function() {
            if($("body").hasClass('fixed-sidebar')) {
                $('.sidebar-collapse').slimScroll({
                  height: 'auto',
                    railOpacity: 0.9
                });
           }
        })

    $(window).bind("load resize click scroll", function() {
        if(!$("body").hasClass('body-small')) {
            fix_height();
        }
    })

    $("[data-toggle=popover]")
        .popover();
});


// For demo purpose - animation css script
function animationHover(element, animation){
    element = $(element);
    element.hover(
        function() {
            element.addClass('animated ' + animation);
        },
        function(){
            //wait for animation to finish before removing classes
            window.setTimeout( function(){
                element.removeClass('animated ' + animation);
            }, 2000);
        });
}

// Minimalize menu when screen is less than 768px
$(function() {
    $(window).bind("load resize", function() {
        if ($(this).width() < 769) {
            $('body').addClass('body-small')
        } else {
            $('body').removeClass('body-small')
        }
    })
})

function SmoothlyMenu() {
    if (!$('body').hasClass('mini-navbar') || $('body').hasClass('body-small')) {
        // Hide menu in order to smoothly turn on when maximize menu
        $('#side-menu').hide();
        // For smoothly turn on menu
        setTimeout(
            function () {
                $('#side-menu').fadeIn(500);
            }, 100);
    } else if ($('body').hasClass('fixed-sidebar')){
        $('#side-menu').hide();
        setTimeout(
            function () {
                $('#side-menu').fadeIn(500);
            }, 300);
    } else {
        // Remove all inline style from jquery fadeIn function to reset menu state
        $('#side-menu').removeAttr('style');
    }
}

// Dragable panels
function WinMove() {
    var element = "[class*=col]";
    var handle = ".ibox-title";
    var connect = "[class*=col]";
    $(element).sortable(
        {
            handle: handle,
            connectWith: connect,
            tolerance: 'pointer',
            forcePlaceholderSize: true,
            opacity: 0.8
        })
        .disableSelection();
};

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

            // ajax request for
            var $form_url = $('.CentreonForm').attr("data-route");

            $.ajax({
                url: '/form/help',
                type: "GET",
                dataType: 'JSON',
                data : {
                    form: $form_url,
                    field: $this.attr("name")
                },
                success : function(data){

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

                        $this.next().css({'display': 'block','padding': '3px'}).html(data.text);
                        $this.css({'padding':'16px 12px 6px'});
                        $parent.removeClass('js-hide-label').addClass('js-unhighlight-label');

                        if ($this.val() == '') {
                            $parent.removeClass('js-hide-label').addClass('js-unhighlight-label');
                        }
                    }
                },
                error : function(error){
                    //console.log(error,' -Help- datas not transfered');
                }
            })

        });
    }



});



