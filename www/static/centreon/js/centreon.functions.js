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
    var lenBookmark = bookmark.length;
    
    for (var i = 0; i < lenBookmark; i++) {
        var $li = $('<li ></li>');
        $li.appendTo($elParent);
        var $link = $('<a></a>').attr('href', bookmark[i].route + '?quick-access-'+ bookmark[i].type + '=' + encodeURIComponent(bookmark[i].quick_access)).text(bookmark[i].label);
        $link.appendTo($li);
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
    clock = moment.unix(clockTimestamp).local();
    var sHeure;
    if (sessionStorage.length > 0 && sessionStorage.getItem("sTimezone") != 'undefined' && sessionStorage.getItem("sTimezone") != "") {
        $(".fa-undo").show();
        
        var f = clock.format(sDefaultFormatDate);
        sHeure = moment(f).tz(sessionStorage.getItem("sTimezone"));
    }  else {
        sHeure = clock;
        $(".fa-undo").hide();
    }
    $('.time .clock').text(sHeure.format("HH:mm:ss"));
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
