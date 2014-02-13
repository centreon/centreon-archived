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
        /* @todo fix bottom 0 */
        $('.bottombar').css('position', 'absolute')
            .css('bottom', 0)
            .css('left', 0)
            .css('width', '100%')
            .css('height', footerAll);
        $('#footer-button i').removeClass('fa-chevron-circle-up').addClass('fa-chevron-circle-down');
    }
}

/* Generate menu */
function generateMenu($elParent, menu) {
    var i = 0;
    var lenMenu = menu.length;
    for (; i < lenMenu; i++) {
        var $li = $('<li></li>');
        $li.appendTo($elParent);
        var $link = $('<a></a>').attr('href', menu[i].url);
        if (menu[i].icon_class != '') {
            $('<i></i>').addClass(menu[i].icon_class).appendTo($link);
        } else if (menu[i].icon_img != '') {
            $('<img>').attr('src',menu[i].icon_img).addClass('').appendTo($link);
        }
        $('<span></span>').text(menu[i].name).appendTo($link);
        $li.append($link);
        if (menu[i].children.length > 0) {
            $('<i></i>').addClass('fa').addClass('fa-plus-square-o').addClass('toggle').addClass('pull-right').appendTo($link);
            $link.addClass('accordion-toggle').addClass('collapsed');
            var $childList = $('<ul></ul>').addClass('collapse').addClass('nav').addClass('submenu').appendTo($li);
            $childList.collapse({ toggle: false });
            generateMenu($childList, menu[i].children);
        }
    }
}

/* Load menu */
function loadMenu(menuUrl, envName) {
    $.ajax({
        'url': menuUrl,
        'data': {
            'menu_id': envName
        },
        'dataType': 'json',
        'type': 'GET',
        'success': function(data, textStatus, jqXHR) {
            if (!data.success) {
                // @todo flash error
                return;
            }
            var $menuUl = $('#menu1');
            $menuUl.html("");
            generateMenu($menuUl, data.menu);
        }
    });
}

/* Add a 0 before if < 10 */
function stringTwoDigit(val) {
    if (val < 10) {
        val = "0" + val;
    }
    return val;
}

/* Clock top */
function topClock() {
    var now = new Date();
    var h = stringTwoDigit(now.getHours());
    var m = stringTwoDigit(now.getMinutes());
    var s = stringTwoDigit(now.getSeconds());

    $('.time .clock').text(h + ':' + m + ':' + s);
    setTimeout(function() { topClock(); }, 500);
}

function alertMessage(msg, cls) {
    var $alertBox = $('#flash-message'); 
    $alertBox.addClass(cls);
    $alertBox.append(msg);
    $alertBox.show();
}

function alertClose() {
    var $alertBox = $('#flash-message'); 
    var $button = $alertBox.find('button.close');
    $alertBox.hide();
    $alertBox.text('');
    $alertBox.append($button);
}
