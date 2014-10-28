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
    //navbarHeight += $('.bottombar').height();
    var contentHeight = $(window).height() - navbarHeight - 3;
    $('#main').css('min-height', contentHeight);
}

/**
 * Resize the left panel
 */
function leftPanelHeight() {
    var mainHeight = $('#main').height();
    var navbarHeight = $('.topbar').height();
    //navbarHeight += $('.bottombar').height();
    var contentHeight = $(window).height() - navbarHeight;
    if (mainHeight > contentHeight) {
        $('#left-panel').css('min-height', mainHeight + 'px');
    } else {
        $('#left-panel').css('min-height', contentHeight + 'px');
    }
}

/* Display full footer */
function toggleFooter() {
    var footerHeight = $('.bottombar > div.label-button').height();
    var footerAll = footerHeight + $('.footer-extended').height();
    if ($('#footer-button i').hasClass('fa-chevron-circle-down')) {
        $('.bottombar').one('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', function() {
            $('.bottombar').removeAttr('style');
            $('.bottombar > div.label-button').removeClass('label-button-active');
            $('#footer-button i').removeClass('fa-chevron-circle-down').addClass('fa-chevron-circle-up');
        });
        $('.bottombar').height(footerHeight);
        if (ie != undefined && ie <= 9) {
            $('.bottombar').removeAttr('style');
            $('.bottombar > div.label-button').removeClass('label-button-active');
            $('#footer-button i').removeClass('fa-chevron-circle-down').addClass('fa-chevron-circle-up');
        }
    } else {
        /* @todo fix bottom 0 */
        $('.bottombar').css('position', 'absolute')
            .css('height', footerAll);
        $('.bottombar > div.label-button').addClass('label-button-active');
        $('#footer-button i').removeClass('fa-chevron-circle-up').addClass('fa-chevron-circle-down');
    }
}

/* Generate menu */
function generateMenu($elParent, menu, subLevelId, childId) {
    var lenMenu = menu.length;

    for (var i = 0; i < lenMenu; i++) {
        var $li = $('<li></li>');
        $li.appendTo($elParent);
        var $link = $('<a></a>').attr('href', menu[i].url).attr('data-menuid', menu[i].menu_id);
        if (menu[i].menu_id == childId) {
            $li.addClass('submenu-active');
        }
        if (menu[i].icon_class != '') {
            $('<i></i>').addClass(menu[i].icon_class).appendTo($link);
        } else if (menu[i].icon_img != '') {
            $('<img>').attr('src',menu[i].icon_img).addClass('').appendTo($link);
        }
        $link.append( " " );
        $('<span></span>').text(menu[i].name).appendTo($link);
        $li.append($link);
        if (menu[i].children.length > 0) {
            var sign = 'fa-plus-square-o';
            var mustToggle = false;
            if (menu[i].menu_id == subLevelId) {
                sign = 'fa-minus-square-o';
                mustToggle = true;
            }
            $('<i></i>').addClass('fa').addClass(sign).addClass('toggle').addClass('pull-right').appendTo($link);
            $link.addClass('accordion-toggle');
            var $childList = $('<ul></ul>');
            var $submenu = $('<ul></ul>').addClass('nav');
            $('<div></div>').addClass('dropdown-submenu').attr("id", "submenu_" + menu[i].menu_id).append($submenu).appendTo($('body'));
            if (menu[i].menu_id == subLevelId) {
                $childList.addClass('in');
            }
            $childList.addClass('collapse').addClass('nav').addClass('submenu').appendTo($li);
            $childList.collapse({ toggle: false });
            generateMenu($childList, menu[i].children, subLevelId, childId);
            generateMenu($submenu, menu[i].children, subLevelId, childId);
        } else if (childId === 0 && menu[i].menu_id == subLevelId) {
            $li.addClass('active');
	    }
    }
}

/* Load menu */
function loadMenu(menuUrl, envId, subLevelId, childId) {
    $.ajax({
        'url': menuUrl,
        'data': {
            'menu_id': envId
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
            generateMenu($menuUl, data.menu, subLevelId, childId);
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

function alertModalMessage(msg, cls) {
    var $alertBox = $('#modal-flash-message'); 
    $alertBox.addClass(cls);
    $alertBox.append(msg);
    $alertBox.show();
}

function alertClose() {
    var $alertBox = $('#flash-message'); 
    var $button = $alertBox.find('button.close');
    var listClass = ['flash', 'alert', 'in', 'fade'];
    $alertBox.hide();
    $alertBox.text('');
    $alertBox.append($button);
    $.each($alertBox[0].classList, function(k, v) {
    	if (-1 === $.inArray(v, listClass)) {
            $alertBox.removeClass(v);
        }
    });
}

function alertModalClose() {
    var $alertBox = $('#modal-flash-message'); 
    var $button = $alertBox.find('button.close');
    var listClass = ['flash', 'alert', 'in', 'fade'];
    $alertBox.hide();
    $alertBox.text('');
    $alertBox.append($button);
    $.each($alertBox[0].classList, function(k, v) {
    	if (-1 === $.inArray(v, listClass)) {
            $alertBox.removeClass(v);
        }
    });
}

/**
 * Used for select2
 */
function select2_formatResult(item) {
    if(!item.id) {
        // return `text` for optgroup
        return '<b>' + item.text + '</b>'; 
    }

    if (item.theming) {
        return item.theming;
    } else {
        return item.text;
    }
}

/**
 * Used for select2
 */
function select2_formatSelection(item) {
    return select2_formatResult(item);
}

