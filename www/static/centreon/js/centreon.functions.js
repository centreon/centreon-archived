var clockTimestamp = moment().utc().unix();

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
    var contentHeight = $(window).height() - navbarHeight - 10; /* -10 is the margin-top of the first content */
    $('#main').css('min-height', contentHeight);
}

function resizeContentLeftPanel()
{
  var sizeContent, mainContainerName,
      navbarHeight = $('.topbar').height(),
      displayHeight = $(window).height() - navbarHeight,
      innerMenu = $('#left-panel').find('nav').height(),
      footerHeight = $('#left-panel .toggle-button').height(),
      contentHeight = 0,
      marginContent = 0;

  if ($('#main > .content-container').length > 0) {
    /* Form */
    contentHeight = $('#main > .content-container > .col-sm-offset-1.col-sm-10').height();
    //console.log(contentHeight);
  } else {
    $('#main').children(':visible').each(function(idx, el) {
      contentHeight += $(el).height();
      contentHeight += +$(el).css('margin-top').replace('px', '');
      contentHeight += +$(el).css('margin-bottom').replace('px', '');
      marginContent += +$(el).css('margin-top').replace('px', '');
      marginContent += +$(el).css('margin-bottom').replace('px', '');
    });
  }

  if (innerMenu + footerHeight + 10 > contentHeight) {
    sizeMenu = innerMenu + footerHeight + 10;
    sizeContent = innerMenu;
  } else {
    sizeMenu = contentHeight + footerHeight + marginContent;
    sizeContent = contentHeight;
  }

  if (sizeMenu < displayHeight) {
    sizeMenu = displayHeight;
    sizeContent = displayHeight - 10 - footerHeight;
  }

  $('#main').off('resize');
  $(window).off('resize');

  $('#main').css('min-height', sizeContent + 'px');
  $('#left-panel').css('min-height', sizeMenu + 'px');

  $('#main').on('resize', function() { resizeContentLeftPanel(); });
  $(window).on('resize', function() { resizeContentLeftPanel(); });
}

/**
 * Resize the left panel
 */
function leftPanelHeight() {
    var mainHeight = $('#main').height();
    var navbarHeight = $('.topbar').height();
    var bottombarHeight = $('.bottombar').height();
    var contentHeight = $(window).height() - navbarHeight;
    if (mainHeight < contentHeight) {
        $('#left-panel').css('min-height', contentHeight + 'px');
    } else {
        var newSize = mainHeight + bottombarHeight + 30; /* Margin top*/
        $('#left-panel').css('min-height', newSize + 'px');
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

/* Load menu */
function loadBookmark(bookmarkUrl) {
    $.ajax({
        'url': bookmarkUrl,
        'dataType': 'json',
        'type': 'GET',
        'success': function(data, textStatus, jqXHR) {
            if (!data.success) {
                // @todo flash error
                return;
            }
            var $bookmarkUl = $('#myBookmark');
            $bookmarkUl.html("");
            generateBookmark($bookmarkUl, data.bookmark);
        }
    });
}

/* Generate bookmark */
function generateBookmark($elParent, bookmark) {
    var lenBookmark= bookmark.length;
    
    for (var i = 0; i < lenBookmark; i++) {
        var $li = $('<li></li>');
        $li.appendTo($elParent);
        var $link = $('<a></a>').attr('href', bookmark[i].route + '?quick-access-'+ bookmark[i].type + '=' + encodeURIComponent(bookmark[i].quick_access));
        $link.append(" ");
        $('<span></span>').text(bookmark[i].label).appendTo($link);
        $li.append($link);
    }
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
    var clock;
    clockTimestamp++;
    clock = moment.unix(clockTimestamp);

    $('.time .clock').text(clock.local().format("HH:mm:ss"));
    setTimeout(function() { topClock(); }, 1000);
}

function alertMessage(msg, cls, timeClose) {
    var $alertBox = $('#flash-message'); 
    $alertBox.addClass(cls);
    $alertBox.append(msg);
    $alertBox.show();
    if ( timeClose !== undefined ) {
      setTimeout(function() {
        alertClose();
      }, timeClose * 1000);
    }
}

function alertModalMessage(msg, cls, timeClose) {
    var $alertBox = $('#modal-flash-message'); 
    $alertBox.addClass(cls);
    $alertBox.append(msg);
    $alertBox.show();
    if ( timeClose !== undefined ) {
      setTimeout(function() {
        alertModalClose();
      }, timeClose * 1000);
    }
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


/**
 * Get a uri parameters
 */
function getUriParametersByName(name) {
  name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
  var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
      results = regex.exec(location.search);
  return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
