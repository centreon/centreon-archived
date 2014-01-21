var ie = (function(){
    var undef,
        v = 3,
        div = document.createElement('div'),
        all = div.getElementsByTagName('i');

        while (
            div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',
            all[0]
        );
    return v > 4 ? v : undef;
}());

/**
 * Function for switch color theme
 */
function switchTheme(theme) {
    if (theme == 'dark') {
        $('body').addClass('dark');
    } else {
        $('body').removeClass('dark');
    }
}

/**
 * Resize content
 */
function resizeContent()
{
    var navbarHeight = $('.topbar').height();
    navbarHeight += $('.bottombar').height();
    var contentHeight = $(window).height() - navbarHeight - 3;
    $('#main').css('min-height', contentHeight);
}

/**
 * Resize the left panel
 */
function leftPanelHeight() {
    var mainHeight = $('#main').height();
    var navbarHeight = $('.topbar').height();
    navbarHeight += $('.bottombar').height();
    var contentHeight = $(window).height() - navbarHeight;
    if (mainHeight > contentHeight) {
        $('#left-panel').css('min-height', mainHeight + 'px');
    } else {
        $('#left-panel').css('min-height', contentHeight + 'px');
    }
}

/* Display environmnent menu */
function displayEnvironmentMenu() {
    $('#environment-menu').show();
    $(window).one('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        $('#environment-menu').hide();
    });
}

/* Display full footer */
function toggleFooter() {
    var footerHeight = $('.footer').height();
    var footerAll = footerHeight + $('.footer-extended').height();
    if ($('#footer-button i').hasClass('fa-chevron-circle-down')) {
        $('.bottombar').one('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', function() {
            $('.bottombar').removeAttr('style');
            $('#footer-button i').removeClass('fa-chevron-circle-down').addClass('fa-chevron-circle-up');
        });
        $('.bottombar').height(footerHeight);
        if (ie != undefined && ie <= 9) {
            $('.bottombar').removeAttr('style');
            $('#footer-button i').removeClass('fa-chevron-circle-down').addClass('fa-chevron-circle-up');
        }
    } else {
        $('.bottombar').css('position', 'absolute')
            .css('bottom', 0)
            .css('left', 0)
            .css('width', '100%')
            .css('height', footerAll);
        $('#footer-button i').removeClass('fa-chevron-circle-up').addClass('fa-chevron-circle-down');
    }
}
