/**
 * DatePicker widget using Prototype and Scriptaculous.
 * (c) 2007-2008 Mathieu Jondet <mathieu@eulerian.com>
 * Eulerian Technologies
 * (c) 2009 Titi Ala'ilima <tigre@pobox.com>
 *
 * DatePicker is freely distributable under the same terms as Prototype.
 *
 * v1.0.0
 */

/***
 * Some general things to keep in mind:
 * - months, when passed around by themselves, typically go from 0 to 11
 */

/**
 * DatePickerUtils object.  Not a class to be instantiated, just an object
 * that holds commonly used values and methods
 */

var DatePickerUtils = {
  oneDayInMs		: 24 * 3600 * 1000,
  _daysInMonth	: [31,28,31,30,31,30,31,31,30,31,30,31],
  /**
   * getMonthDays : given the year and month find the number of days.
   */
  getMonthDays	: function ( year, month ) {
    if (((0 == (year%4)) && 
	 ( (0 != (year%100)) || (0 == (year%400)))) && (month == 1))
      return 29;
    return this._daysInMonth[month];
  },
  /**
   * convertDate : turn an ANSI date or (real) number of days relative to now
   * and returns a Date object.
   */
  parseDate : function(dateString) {
    var dateObj = DatePickerUtils.ansiDateToObject(dateString);
    if (!dateObj) {
      var relDate = parseFloat(dateString);
      dateObj = new Date();
      dateObj.setTime(dateObj.getTime() + dateString * this.oneDayInMs);
    }
    return dateObj;
  },
  /**
   * dateObjectToAnsi - note: year is padded to 4 digits, just in case
   */
  dateObjectToAnsi: function(dateObj) {
    if (!dateObj) return null;
    return dateObj.getFullYear().toPaddedString(4) + '-' +
        (dateObj.getMonth() + 1).toPaddedString(2) + '-' +
        dateObj.getDate().toPaddedString(2);
  },
  /**
   * ansiDateToObject - returns null of it fails to parse
   */
  ansiDateToObject: function(ansiDate) {
    var dateObj = null;
    var parsedDate = String(ansiDate).match(/^(\d+)-0*(\d+)-0*(\d+)$/);
    if (parsedDate)
      dateObj = new Date(parsedDate[1],parsedDate[2] - 1,parsedDate[3]);
    return dateObj;
  },
  /**
   * yearMonthToAnsiStub takes year and (0-based) month and returns the
   * ANSI date minus the two digit date-of-month, e.g. "2009-03-"
   */
  yearMonthToAnsiStub: function(year, month) {
    return year.toPaddedString(4)+'-'+(month+1).toPaddedString(2)+'-';
  },
  /**
   * Standard default filter generators which return DatePickerFilter objects
   */
  /**
   * noDatesBefore takes an ISO date string or a real number indicating how
   * many days relative to the current time to set the cutoff of valid dates.
   * If any time during a given date is valid, the date is allowed.
   */
  noDatesBefore: function (firstDate) {
    return new DatePickerFilter(
      function(year, month) {
	/* Perform our date comparisons with ANSI/ISO date strings */
        var testDate = DatePickerUtils.dateObjectToAnsi(
			     DatePickerUtils.parseDate(firstDate));
	var dateFilter = new Array();
	var monthDays = DatePickerUtils.getMonthDays(year, month);
	var calDate = DatePickerUtils.yearMonthToAnsiStub(year,month);
	for (var i = 1; i <= monthDays; i++)
	  dateFilter[i] = (testDate > (calDate+i.toPaddedString(2)) );

	return dateFilter;
      },
      function(year, month) {
	/* Perform our date comparisons with ANSI/ISO date strings */
        var testDate = 
	  DatePickerUtils.dateObjectToAnsi(DatePickerUtils.parseDate(firstDate));
	var calDate = DatePickerUtils.yearMonthToAnsiStub(year,month) +
	  DatePickerUtils.getMonthDays(year,month);
	return (testDate <= calDate);
      }
      );  
  },
  /**
   * noDatesAfter takes an ISO date string or a real number indicating how
   * many days relative to the current time to set the cutoff of valid dates.
   * If any time during a given date is valid, the date is allowed.
   */
  noDatesAfter: function (firstDate) {
    return new DatePickerFilter(
      function(year, month) {
	/* Perform our date comparisons with ANSI/ISO date strings */
        var testDate = DatePickerUtils.dateObjectToAnsi(
			     DatePickerUtils.parseDate(firstDate));
	var dateFilter = new Array();
	var monthDays = DatePickerUtils.getMonthDays(year, month);
	var calDate = DatePickerUtils.yearMonthToAnsiStub(year,month);
	for (var i = 1; i <= monthDays; i++)
	  dateFilter[i] = (testDate < (calDate+i.toPaddedString(2)) );

	return dateFilter;
      },
      function(year, month) {
	/* Perform our date comparisons with ANSI/ISO date strings */
        var testDate = 
	  DatePickerUtils.dateObjectToAnsi(DatePickerUtils.parseDate(firstDate));
	var calDate = DatePickerUtils.yearMonthToAnsiStub(year,month) + '01';
	return (testDate >= calDate);
      }
      );  
  },
  /**
   * noWeekends returns a filter that excludes Saturdays and Sundays.  No
   * time or demand at the moment for something that deals with locale-specific
   * weekend configurations (e.g. Fri, Sat in the Middle East).
   */
  noWeekends: function () {
    return new DatePickerFilter(
      function(year, month) {
	var dateFilter = new Array();
	var monthDays = DatePickerUtils.getMonthDays(year, month);
	var calDate = new Date(year,month,1);
	for (var i = 1; i <= monthDays; calDate.setFullYear(year,month,++i))
	  dateFilter[i] = ((calDate.getDay() % 6) == 0); // 0 = Sun, 6 = Sat
	return dateFilter;
      },
      null
      );  
  }
}

/**
 * DatePickerFormatter class for matching and stringifying dates.
 *
 * By Arturas Slajus <x11@arturaz.net>.
 */
var DatePickerFormatter = Class.create();
DatePickerFormatter.prototype = {
  /**
   * Create a DatePickerFormatter.
   *
   * format: specify a format by passing 3 value array consisting of
   *   "yyyy", "mm", "dd". Default: ["yyyy", "mm", "dd"].
   *
   * separator: string for splitting the values. Default: "-".
   *
   * Use it like this:
   *   var df = new DatePickerFormatter(["dd", "mm", "yyyy"], "/");
   *   df.current_date();
   *   df.match("7/7/2007");
   */
  initialize: function(format, separator) {
    if (Object.isUndefined(format))
      format = ["yyyy", "mm", "dd"];
    if (Object.isUndefined(separator))
      separator = "-";

    this._format 	= format;
    this.separator	= separator;
                
    this._formatYearIndex	= format.indexOf("yyyy");
    this._formatMonthIndex= format.indexOf("mm");
    this._formatDayIndex	= format.indexOf("dd");
                
    this._yearRegexp	= /^\d{4}$/;
    this._monthRegexp 	= /^0\d|1[012]|\d$/;
    this._dayRegexp 	= /^0\d|[12]\d|3[01]|\d$/;
  },
    
  /**
   * Match a string against date format.
   * Returns: [year, month, day]
   */
  match: function(str) {
    var d = str.split(this.separator);
        
    if (d.length < 3)
      return false;
        
    var year = d[this._formatYearIndex].match(this._yearRegexp);
    if (year) { year = year[0] } else { return false }
    var month = d[this._formatMonthIndex].match(this._monthRegexp);
    if (month) { month = month[0] } else { return false }
    var day = d[this._formatDayIndex].match(this._dayRegexp);
    if (day) { day = day[0] } else { return false }
        
    return [year, month, day];
  },
    
  /**
   * Return current date according to format.
   */
  currentDate: function() {
    var d = new Date;
    return this.dateToString(
			     d.getFullYear(),
			     d.getMonth() + 1,
			     d.getDate()
			     );
  },
    
  /**
   * Return a stringified date according to format.  Note, month is from
   * 1 to 12 here.
   */
  dateToString: function(year, month, day, separator) {
    if (Object.isUndefined(separator))
      separator = this.separator;

    var a = [0, 0, 0];
    a[this._formatYearIndex]	= year;
    a[this._formatMonthIndex] 	= month.toPaddedString(2);
    a[this._formatDayIndex] 	= day.toPaddedString(2);
        
    return a.join(separator);
  }
}; 

/**
 * DatePickerFilter
 * Titi Ala'ilima <tigre@pobox.com>
 */

var DatePickerFilter = Class.create();

DatePickerFilter.prototype = {
  /* A flexible way of blocking dates off from being selected.  Should
     be able to go so far as to hook it into an AJAX-based holiday filter,
     should anyone be kind enough to build one.  Note that validDates and
     validMonthP take 0-based months.
  */
  initialize : function (dateFilterFunction, monthFilterFunction) {
    if (dateFilterFunction) this.badDates = dateFilterFunction;
    if (monthFilterFunction) this.validMonthP = monthFilterFunction;
  },
  /**
   * badDates is a method which takes a fear and (0-based) month and
   * returns an array indexed by the (1-based) date, with value true if
   * the filter says this date is not allowed.  (This way,
   * an empty array equates to an unfiltered month.)
   */
  badDates : null,
  /**
   * validMonthP is a method which takes a fear and (0-based) month and
   * returns a Boolean saying whether or not the month is allowed by this
   * filter
   */
  validMonthP : null,
  /* Using "append" we can string filters together, such as "before date a",
     "after date b", and "not on weekends".  Note: this changes the current
     DatePickerFilter rather than returning a new one.
     Caveat Integrator: This uses closures. These can cause memory leaks (in
     IE) so be careful!
  */
  append : function (nextFilter) {
    if (!this.badDates)
      this.badDates = nextFilter.badDates;
    else if (nextFilter.badDates) {
      var firstBadDates = this.badDates;
      this.badDates = function (year, month) {
	  var results1 = firstBadDates(year,month);
	  var results2 = nextFilter.badDates(year,month);
	  /* An element-wise "or", since if it's bad in one filter, it's
	     bad for the aggregate. */
	  for (var i = 0; i < results1.length; i++)
	    results1[i] = results1[i] || results2[i];
	  return results1;
	};
    }
    if (!this.validMonthP)
      this.validMonthP = nextFilter.validMonthP;
    else if (nextFilter.validMonthP) {
      var firstValidMonthP = this.validMonthP;
      this.validMonthP = function (year, month) {
	  return firstValidMonthP(year,month) && nextFilter.validMonthP(year,month);
      };
    }
    return this; // Just so we can chain filters inline
  }
};


/**
 * DatePicker
 */

var DatePicker	= Class.create();

DatePicker.prototype	= {
  Version	: '1.0.0',
  _relative	: null,
  _div		: null,
  _zindex	: 1,
  _keepFieldEmpty: false,
  _dateFormat	: null,
  /* language */
  _language	: 'en',
  _language_month	: $H({
      'fr' : [ 'Janvier', 'F&#233;vrier', 'Mars', 'Avril', 'Mai', 'Juin', 
	       'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre',
	       'D&#233;cembre' ],
      'en' : [ 'January', 'February', 'March', 'April', 'May', 'June', 'July',
	       'August', 'September', 'October', 'November', 'December' ],
      'sp' : [ 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
	       'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre' ],
      'it' : [ 'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno',
	       'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre',
	       'Dicembre' ],
      'de' : [ 'Januar', 'Februar', 'M&#228;rz', 'April', 'Mai', 'Juni', 'Juli',
	       'August', 'September', 'Oktober', 'November', 'Dezember' ],
      'pt' : [ 'Janeiro', 'Fevereiro', 'Mar&#231;o', 'Abril', 'Maio', 'Junho',
	       'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro' ],
      'hu' : [ 'Janu&#225;r', 'Febru&#225;r', 'M&#225;rcius', '&#193;prilis', 
	       'M&#225;jus', 'J&#250;nius', 'J&#250;lius', 'Augusztus',
	       'Szeptember', 'Okt&#243;ber', 'November', 'December' ],
      'lt' : [ 'Sausis', 'Vasaris', 'Kovas', 'Balandis', 'Gegu&#382;&#279;',
	       'Bir&#382;elis', 'Liepa', 'Rugj&#363;tis', 'Rus&#279;jis',
	       'Spalis', 'Lapkritis', 'Gruodis' ],
      'nl' : [ 'januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli',
	       'augustus', 'september', 'oktober', 'november', 'december' ],
      'dk' : [ 'Januar', 'Februar', 'Marts', 'April', 'Maj', 'Juni', 'Juli',
	       'August', 'September', 'Oktober', 'November', 'December' ],
      'no' : [ 'Januar', 'Februar', 'Mars', 'April', 'Mai', 'Juni', 'Juli',
	       'August', 'September', 'Oktober', 'November', 'Desember' ],
      'lv' : [ 'Janv&#257;ris', 'Febru&#257;ris', 'Marts', 'Apr&#299;lis',
	       'Maijs', 'J&#363;nijs', 'J&#363;lijs', 'Augusts', 'Septembris',
	       'Oktobris', 'Novembris', 'Decemberis' ],
      'ja' : [ '1&#26376;', '2&#26376;', '3&#26376;', '4&#26376;', '5&#26376;',
	       '6&#26376;', '7&#26376;', '8&#26376;', '9&#26376;',
	       '10&#26376;', '11&#26376;', '12&#26376;' ],
      'fi' : [ 'Tammikuu', 'Helmikuu', 'Maaliskuu', 'Huhtikuu', 'Toukokuu',
	       'Kes&#228;kuu', 'Hein&#228;kuu', 'Elokuu', 'Syyskuu', 'Lokakuu',
	       'Marraskuu', 'Joulukuu' ],
      'ro' : [ 'Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Junie',
	       'Julie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie',
	       'Decembrie' ],
      'zh' : [ '1&#32;&#26376;', '2&#32;&#26376;', '3&#32;&#26376;', 
	       '4&#32;&#26376;', '5&#32;&#26376;', '6&#32;&#26376;',
	       '7&#32;&#26376;', '8&#32;&#26376;', '9&#32;&#26376;',
	       '10&#26376;', '11&#26376;', '12&#26376;'],
      'sv' : [ 'Januari', 'Februari', 'Mars', 'April', 'Maj', 'Juni', 'Juli',
	       'Augusti', 'September', 'Oktober', 'November', 'December' ],
      'pl' : [ 'Stycze\u0144', 'Luty', 'Marzec', 'Kwiecie\u0144', 'Maj',
               'Czerwiec', 'Lipiec', 'Sierpie\u0144', 'Wrzesie\u0144',		  
               'Pa\u017adziernik', 'Listopad', 'Grudzie\u0144']
	}),
  _language_day	: $H({
      'fr'	: [ 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim' ],
	'en'	: [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ],
	'sp'	: [ 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'S&#224;b', 'Dom' ],
	'it'	: [ 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom' ],
	'de'	: [ 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam', 'Son' ],
	'pt'	: [ 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S&#225;', 'Dom' ],
	'hu'	: [ 'H&#233;', 'Ke', 'Sze', 'Cs&#252;', 'P&#233;', 'Szo', 'Vas' ],
	'lt'  : [ 'Pir', 'Ant', 'Tre', 'Ket', 'Pen', '&Scaron;e&scaron;', 'Sek' ],
	'nl'	: [ 'ma', 'di', 'wo', 'do', 'vr', 'za', 'zo' ],
	'dk'	: [ 'Man', 'Tir', 'Ons', 'Tor', 'Fre', 'L&#248;r', 'S&#248;n' ],
	'no'	: [ 'Man', 'Tir', 'Ons', 'Tor', 'Fre', 'L&#248;r', 'Sun' ],
	'lv'	: [ 'P', 'O', 'T', 'C', 'Pk', 'S', 'Sv' ],
	'ja'	: [ '&#26376;', '&#28779;', '&#27700;', '&#26408;', '&#37329;', 
		    '&#22303;', '&#26085;' ],
	'fi'	: [ 'Ma', 'Ti', 'Ke', 'To', 'Pe', 'La', 'Su' ],
	'ro'	: [ 'Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'Sam', 'Dum' ],
	'zh'	: [ '&#21608;&#19968;', '&#21608;&#20108;', '&#21608;&#19977;', 
		    '&#21608;&#22235;', '&#21608;&#20116;', '&#21608;&#20845;', 
		    '&#21608;&#26085;' ],
	'sv'	: [ 'M&#229;n', 'Tis', 'Ons', 'Tor', 'Fre', 'L&#246;r', 
		    'S&#246;n' ],
        'pl'	: ['Pon', 'Wt', '\u015ar', 'Czw', 'Pt', 'Sob', 'Nie']
	}),
  _language_close	: $H({
      'fr'	: 'fermer',
	'en'	: 'close',
	'sp'	: 'cierre',
	'it'	: 'fine',
	'de'	: 'schliessen',
	'pt'	: 'fim',
	'hu'	: 'bez&#225;r',
	'lt'	: 'u&#382;daryti',
	'nl'	: 'sluiten',
	'dk'	: 'luk',
	'no'	: 'lukk',
	'lv'	: 'aizv&#275;rt',
	'ja'	: '&#38281;&#12376;&#12427;',
	'fi'	: 'sulje',
	'ro'	: 'inchide',
	'zh'	: '&#20851;&#32;&#38381',
	'sv'	: 'st&#228;ng',
	'pl'	: 'zamknij'
	}),
  _language_date_format : $H({
      'en': [ ["mm", "dd", "yyyy"], "/" ],
	'lt': [ ["yyyy", "mm", "dd"], "-" ],
	'fr': [ ["dd", "mm", "yyyy"], "/" ],
	'sp': [ ["dd", "mm", "yyyy"], "/" ],
	'it': [ ["dd", "mm", "yyyy"], "/" ],
	'de': [ ["dd", "mm", "yyyy"], "/" ],
	'pt': [ ["dd", "mm", "yyyy"], "/" ],
	'hu': [ ["dd", "mm", "yyyy"], "/" ],
	'nl': [ ["dd", "mm", "yyyy"], "/" ],
	'dk': [ ["dd", "mm", "yyyy"], "/" ],
	'no': [ ["dd", "mm", "yyyy"], "/" ],
	'lv': [ ["dd", "mm", "yyyy"], "/" ],
	'ja': [ ["yyyy", "mm", "dd"], "-" ],
	'fi': [ ["dd", "mm", "yyyy"], "." ],
	'ro': [ ["dd", "mm", "yyyy"], "/" ],
	'zh': [ ["yyyy", "mm", "dd"], "-" ],
	'sv': [ ["dd", "mm", "yyyy"], "/" ],
        'pl': [ ["yyyy", "mm", "dd"], "-" ]
	}),
  /* date manipulation */
  _todayDate		: new Date(),
  _currentDate		: null,
  _clickCallback		: Prototype.emptyFunction,
  _cellCallback		: Prototype.emptyFunction,
  _dateFilter : new DatePickerFilter(),
  _id_datepicker		: null,
  /* positionning */
  _topOffset		: 30,
  _leftOffset		: 0,
  _isPositionned		: false,
  _relativePosition 	: true,
  _setPositionTop 	: 0,
  _setPositionLeft	: 0,
  _bodyAppend		: false,
  /* Effects Adjustment */
  _showEffect		: "appear", 
  _showDuration		: 0.2,
  _enableShowEffect 	: true,
  _closeEffect		: "fade", 
  _closeEffectDuration	: 0.2,
  _enableCloseEffect 	: true,
  _closeTimer		: null,
  _enableCloseOnBlur	: false,
  /* afterClose : called when the close function is executed */
  _afterClose	: Prototype.emptyFunction,
  /* return the name of current month in appropriate language */
  getMonthLocale	: function ( month ) {
    return	this._language_month.get(this._language)[month];
  },
  getLocaleClose	: function () {
    return	this._language_close.get(this._language);
  },
  _initCurrentDate : function () {
    /* Create the DateFormatter */
    if (!this._dateFormat)
      this._dateFormat = this._language_date_format.get(this._language);
    this._df = new DatePickerFormatter(this._dateFormat[0], this._dateFormat[1]);
    /* check if value in field is proper, if not set to today */
    this._currentDate = $F(this._relative);
    if (! this._df.match(this._currentDate)) {
      this._currentDate = this._df.currentDate();
      /* set the field value ? */
      if (!this._keepFieldEmpty)
	$(this._relative).value = this._currentDate;
    }
    var a_date = this._df.match(this._currentDate);
    this._currentYear 	= Number(a_date[0]);
    this._currentMonth	= Number(a_date[1]) - 1;
    this._currentDay	= Number(a_date[2]);
  },
  /* init */
  initialize	: function ( h_p ) {
    /* arguments */
    this._relative= h_p["relative"];
    if (h_p["language"])
      this._language = h_p["language"];
    this._zindex	= ( h_p["zindex"] ) ? parseInt(Number(h_p["zindex"])) : 1;
    if (!Object.isUndefined(h_p["keepFieldEmpty"]))
      this._keepFieldEmpty	= h_p["keepFieldEmpty"];
    if (Object.isFunction(h_p["clickCallback"])) 
      this._clickCallback	= h_p["clickCallback"];
    if (!Object.isUndefined(h_p["leftOffset"]))
      this._leftOffset	= parseInt(h_p["leftOffset"]);
    if (!Object.isUndefined(h_p["topOffset"]))
      this._topOffset	= parseInt(h_p["topOffset"]);
    if (!Object.isUndefined(h_p["relativePosition"]))
      this._relativePosition = h_p["relativePosition"];
    if (!Object.isUndefined(h_p["showEffect"]))
      this._showEffect 	= h_p["showEffect"];
    if (!Object.isUndefined(h_p["enableShowEffect"]))
      this._enableShowEffect	= h_p["enableShowEffect"];
    if (!Object.isUndefined(h_p["showDuration"]))
      this._showDuration 	= h_p["showDuration"];
    if (!Object.isUndefined(h_p["closeEffect"]))
      this._closeEffect 	= h_p["closeEffect"];
    if (!Object.isUndefined(h_p["enableCloseEffect"]))
      this._enableCloseEffect	= h_p["enableCloseEffect"];
    if (!Object.isUndefined(h_p["closeEffectDuration"]))
      this._closeEffectDuration = h_p["closeEffectDuration"];
    if (Object.isFunction(h_p["afterClose"]))
      this._afterClose	= h_p["afterClose"];
    if (!Object.isUndefined(h_p["externalControl"]))
      this._externalControl= h_p["externalControl"];
    if (!Object.isUndefined(h_p["dateFormat"])) 
      this._dateFormat	= h_p["dateFormat"];
    if (Object.isFunction(h_p["cellCallback"]))
      this._cellCallback	= h_p["cellCallback"];
    this._setPositionTop	= ( h_p["setPositionTop"] ) ? 
    parseInt(Number(h_p["setPositionTop"])) : 0;
    this._setPositionLeft	= ( h_p["setPositionLeft"] ) ? 
    parseInt(Number(h_p["setPositionLeft"])) : 0;
    if (!Object.isUndefined(h_p["enableCloseOnBlur"]) && h_p["enableCloseOnBlur"])
      this._enableCloseOnBlur	= true;
    if (!Object.isUndefined(h_p["dateFilter"]) && h_p["dateFilter"])
      this._dateFilter = h_p["dateFilter"];
    // Backwards compatibility
    if (!Object.isUndefined(h_p["disablePastDate"]) && h_p["disablePastDate"])
      this._dateFilter.append(DatePickerUtils.noDatesBefore(0));
    else if (!Object.isUndefined(h_p["disableFutureDate"]) && h_p["disableFutureDate"])
      this._dateFilter.append(DatePickerUtils.noDatesAfter(0));
    this._id_datepicker		= 'datepicker-'+this._relative;
    this._id_datepicker_prev	= this._id_datepicker+'-prev';
    this._id_datepicker_next	= this._id_datepicker+'-next';
    this._id_datepicker_hdr	= this._id_datepicker+'-header';
    this._id_datepicker_ftr	= this._id_datepicker+'-footer';
    
    /* build up calendar skel */
    this._div = new Element('div', { 
      id : this._id_datepicker,
      className : 'datepicker',
      style : 'display: none; z-index:'+this._zindex });
    this._div.innerHTML = '<table><thead><tr><th width="10px" id="'+this._id_datepicker_prev+'" style="cursor: pointer;">&nbsp;&lt;&lt;&nbsp;</th><th id="'+this._id_datepicker_hdr+'" colspan="5"></th><th width="10px" id="'+this._id_datepicker_next+'" style="cursor: pointer;">&nbsp;&gt;&gt;&nbsp;</th></tr></thead><tbody id="'+this._id_datepicker+'-tbody"></tbody><tfoot><tr><td colspan="7" id="'+this._id_datepicker_ftr+'"></td></tr></tfoot></table>';
    /* finally declare the event listener on input field */
    Event.observe(this._relative, 
		  'click', this.click.bindAsEventListener(this), false);
    /* need to append on body when doc is loaded for IE */
    document.observe('dom:loaded', this.load.bindAsEventListener(this), false);
    /* automatically close when blur event is triggered */
    if ( this._enableCloseOnBlur ) {
      Event.observe(this._relative, 'blur', function (e) { 
		      if (!this._closeTimer) this._closeTimer = this.close.bind(this).delay(1); 
		    }.bindAsEventListener(this));
      Event.observe(this._div, 'click', function (e) { 
		      Field.focus(this._relative);
		      this.checkClose.bind(this).delay(0.1);
		    }.bindAsEventListener(this));
    }
  },
  /**
   * load	: called when document is fully-loaded to append datepicker
   *		  to main object.
   */
  load		: function () {
    /* if externalControl defined set the observer on it */
    if (this._externalControl) 
      Event.observe(this._externalControl, 'click',
		    this.click.bindAsEventListener(this), false);
    /* append to page */
    if (this._relativeAppend) {
   /* append to parent node */
      if ($(this._relative).parentNode) {
	this._div.innerHTML = this._wrap_in_iframe(this._div.innerHTML);
	$(this._relative).parentNode.appendChild( this._div );
      }
    } else {
      /* append to body */
      var body	= document.getElementsByTagName("body").item(0);
      if (body) {
	this._div.innerHTML = this._wrap_in_iframe(this._div.innerHTML);
	body.appendChild(this._div);
   }
      if ( this._relativePosition ) {
	var a_pos = Element.cumulativeOffset($(this._relative));
	this.setPosition(a_pos[1], a_pos[0]);
      } else {
	if (this._setPositionTop || this._setPositionLeft)
	  this.setPosition(this._setPositionTop, this._setPositionLeft);
      }
    }
    /* init the date in field if needed */
    this._initCurrentDate();
    /* set the close locale content */
    $(this._id_datepicker_ftr).innerHTML = this.getLocaleClose();
    /* declare the observers for UI control */
    Event.observe($(this._id_datepicker_prev), 
		  'click', this.prevMonth.bindAsEventListener(this), false);
    Event.observe($(this._id_datepicker_next), 
		  'click', this.nextMonth.bindAsEventListener(this), false);
    Event.observe($(this._id_datepicker_ftr), 
		  'click', this.close.bindAsEventListener(this), false);
    Event.observe($(document), 
		  'click', this.documentClick.bindAsEventListener(this), false);
  },
  /* hack for buggy form elements layering in IE */
  _wrap_in_iframe	: function ( content ) {
    return	( Prototype.Browser.IE ) ?
    "<div style='height:167px;width:185px;background-color:white;align:left'><iframe width='100%' height='100%' marginwidth='0' marginheight='0' frameborder='0' src='about:blank' style='filter:alpha(Opacity=50);'></iframe><div style='position:absolute;background-color:white;top:2px;left:2px;width:180px'>" + content + "</div></div>" : content;
  },
  /**
   * visible	: return the visibility status of the datepicker.
   */
  visible	: function () {
    return	$(this._id_datepicker).visible();
  },
  /**
   * click	: called when input element is clicked
   */
  click		: function () {
    /* init the datepicker if it doesn't exists */
    if ( $(this._id_datepicker) == null ) this.load();
    if (!this._isPositionned && this._relativePosition) {
      /* position the datepicker relatively to element */
      var a_lt = Element.cumulativeOffset($(this._relative));
      $(this._id_datepicker).setStyle({
	  'left'	: Number(a_lt[0]+this._leftOffset)+'px',
	    'top'	: Number(a_lt[1]+this._topOffset)+'px'
	    });
      this._isPositionned	= true;
    }
    if (!this.visible()) {
      this._initCurrentDate();
      this._redrawCalendar();
    }
    /* eval the clickCallback function */
    eval(this._clickCallback());
    /* Effect toggle to fade-in / fade-out the datepicker */
    if ( this._enableShowEffect ) {
      new Effect.toggle(this._id_datepicker, 
			this._showEffect, { duration: this._showDuration });
    } else {
      $(this._id_datepicker).show();
    }
  },
  /**
   * close	: called when the datepicker is closed
   */
  close		: function () {
    // ignore requests to close if already closed:
    if (!this.visible())
      return;
    this.checkClose();
    if ( this._enableCloseEffect ) {
      switch(this._closeEffect) {
	case 'puff': 
	new Effect.Puff(this._id_datepicker, { 
	  duration : this._closeEffectDuration });
	break;
	case 'blindUp': 
	new Effect.BlindUp(this._id_datepicker, { 
	  duration : this._closeEffectDuration });
	break;
	case 'dropOut': 
	new Effect.DropOut(this._id_datepicker, { 
	  duration : this._closeEffectDuration }); 
	break;
	case 'switchOff': 
	new Effect.SwitchOff(this._id_datepicker, { 
	  duration : this._closeEffectDuration }); 
	break;
	case 'squish': 
	new Effect.Squish(this._id_datepicker, { 
	  duration : this._closeEffectDuration });
	break;
	case 'fold': 
	new Effect.Fold(this._id_datepicker, { 
	  duration : this._closeEffectDuration });
	break;
	case 'shrink': 
	new Effect.Shrink(this._id_datepicker, { 
	  duration : this._closeEffectDuration });
	break;
	default: 
	new Effect.Fade(this._id_datepicker, { 
	  duration : this._closeEffectDuration });
	break;
      };
    } else {
      $(this._id_datepicker).hide();
    }
    eval(this._afterClose());
  },
  /**
   * checkClose	: called to check whether datepicker is set to close when it's clicked (due to enableCloseOnBlur)
   * Thanks to firetech87
   */
  checkClose	: function () {
    if (this._closeTimer) {
      window.clearTimeout(this._closeTimer);
      this._closeTimer = null;
    }
  },
   /**
    * documentClick  : called when user clicked anywhere in the document
    */
  documentClick     : function (event) {
    var source = event.element();
    if (source != this._div && source != $(this._relative) && source != $(this._externalControl) &&
	!source.descendantOf(this._div)) 
      this.close();
  },
  /**
   * setDateFormat
   */
  setDateFormat	: function ( format, separator ) {
    if (Object.isUndefined(format))
      format	= this._dateFormat[0];
    if (Object.isUndefined(separator))
      separator	= this._dateFormat[1];
    this._dateFormat	= [ format, separator ];
  },
  /**
   * setPosition	: set the position of the datepicker.
   *  param : t=top | l=left
   */
  setPosition	: function ( t, l ) {
    var h_pos	= { 'top' : '0px', 'left' : '0px' };
    if (!Object.isUndefined(t))
      h_pos['top']	= Number(t)+this._topOffset+'px';
    if (!Object.isUndefined(l))
      h_pos['left']= Number(l)+this._leftOffset+'px';
    $(this._id_datepicker).setStyle(h_pos);
    this._isPositionned	= true;
  },
  /**
   * _buildCalendar	: draw the days array for current date
   */
  _buildCalendar		: function () {
    var _self	= this;
    var tbody	= $(this._id_datepicker+'-tbody');
    try {
      while ( tbody.hasChildNodes() )
	tbody.removeChild(tbody.childNodes[0]);
    } catch ( e ) {};
    /* generate day headers */
    var trDay	= new Element('tr');
    this._language_day.get(this._language).each( function ( item ) {
						   var td	= new Element('td');
						   td.innerHTML	= item;
						   td.className	= 'wday';
						   trDay.appendChild( td );
						 });
    tbody.appendChild( trDay );
    /* generate the content of days */
    
    /* build-up days matrix */
    var a_d	= [ [ 0, 0, 0, 0, 0, 0, 0 ] ,[ 0, 0, 0, 0, 0, 0, 0 ]
		    ,[ 0, 0, 0, 0, 0, 0, 0 ], [ 0, 0, 0, 0, 0, 0, 0 ], [ 0, 0, 0, 0, 0, 0, 0 ]
		    ,[ 0, 0, 0, 0, 0, 0, 0 ]
		    ];
    var currentMonth	= this._currentMonth;
    var currentYear	= this._currentYear;
    /* set date at beginning of month to display */
    var d		= new Date(currentYear, currentMonth, 1, 12);
    /* start the day list on monday */
    var startIndex	= (d.getDay() + 6) % 7;
    var nbDaysInMonth	= DatePickerUtils.getMonthDays(currentYear, currentMonth);
    var daysIndex		= 1;
    var badDates = (this._dateFilter.badDates) ? this._dateFilter.badDates(currentYear, currentMonth) : [];

    /* The first week */
    for ( var j = startIndex; j < 7; j++ ) {
      a_d[0][j]	= { 
	d : daysIndex
	,m : currentMonth
	,y : currentYear 
	,b : badDates[daysIndex]
      };
      daysIndex++;
    }
    /* Fill in days before the current month starts */
    var a_prevMY	= this._prevMonthYear();
    var nbDaysInMonthPrev	= DatePickerUtils.getMonthDays(a_prevMY[1], a_prevMY[0]);
    for ( var j = 0; j < startIndex; j++ ) {
      a_d[0][j]	= { 
	d : Number(nbDaysInMonthPrev - startIndex + j + 1) 
	,m : Number(a_prevMY[0])
	,y : a_prevMY[1]
	,c : 'outbound'
	,b : true
      };
    }
    /* Now the remaining weeks */
    var switchNextMonth	= false;
    for ( var i = 1; i < 6; i++ ) {
      for ( var j = 0; j < 7; j++ ) {
	a_d[i][j]	= { 
	  d : daysIndex
	  ,m : currentMonth
	  ,y : currentYear
	  ,c : ( switchNextMonth )
	       ? 'outbound' :
	       ( 
		((daysIndex == this._todayDate.getDate()) &&
		 (currentMonth  == this._todayDate.getMonth()) &&
		 (currentYear == this._todayDate.getFullYear())) ? 'today' : null)
	  ,b : switchNextMonth || badDates[daysIndex]
	};
	daysIndex++;
	/* if at the end of the month : reset counter */
	if (daysIndex > nbDaysInMonth ) {
	  daysIndex	= 1;
	  switchNextMonth = true;
	  if (this._currentMonth + 1 > 11 ) {
	    currentMonth = 0;
	    currentYear += 1;
	  } else {
	    currentMonth += 1;
	  }
	}
      }
    }
    /* now generate the table cells for the dates */
    for ( var i = 0; i < 6; i++ ) {
      var tr	= new Element('tr');
      for ( var j = 0; j < 7; j++ ) {
	var h_ij = a_d[i][j];
	var td	= new Element('td');
	/* id is : datepicker-day-mon-year or depending on language other way */
	/* don't forget to add 1 on month for proper formmatting */
	var id	= $A([
		      this._relative,
		      this._df.dateToString(h_ij["y"], h_ij["m"]+1, h_ij["d"], '-')
		      ]).join('-');
	/* set id and classname for cell if exists */
	td.setAttribute('id', id);
	if (h_ij["c"])
	  td.className	= h_ij["c"];
	this._bindCellOnClick( td, h_ij["b"], h_ij["c"] );
	td.innerHTML= h_ij["d"];
	tr.appendChild( td );
      }
      tbody.appendChild( tr );
    }
    return	tbody;
  },
  /**
   * _bindCellOnClick	: bind the cell onclick depending on status.
   */
  _bindCellOnClick	: function ( td, badDateP, cellClass ) {
    if ( badDateP ) {
      td.className= ( cellClass ) ? 'nclick_' + cellClass : 'nclick';
    } else {
      /* Create a closure so we have access to the DatePicker object */
      var _self	= this;
      td.onclick	= function () { 
	$(_self._relative).value = String($(this).readAttribute('id')
					  ).replace(_self._relative+'-','').replace(/-/g, _self._df.separator);
	/* if we have a cellCallback defined call it and pass it the cell */
	if (_self._cellCallback)
	  _self._cellCallback(this);
	_self.close(); 
      };
    }
  },
  /**
   * nextMonth	: redraw the calendar content for next month.
   */
  _nextMonthYear	: function () {
    var c_mon	= this._currentMonth;
    var c_year	= this._currentYear;
    if (c_mon + 1 > 11) {
      c_mon	= 0;
      c_year	+= 1;
    } else {
      c_mon	+= 1;
    }
    return	[ c_mon, c_year ];
  },
  nextMonth	: function () {
    this._maybeRedrawMonth(this._nextMonthYear());
  },
  /**
   * prevMonth	: redraw the calendar content for previous month.
   */
  _prevMonthYear	: function () {
    var c_mon	= this._currentMonth;
    var c_year	= this._currentYear;
    if (c_mon - 1 < 0) {
      c_mon	= 11;
      c_year	-= 1;
    } else {
      c_mon	-= 1;
    }
    return	[ c_mon, c_year ];
  },
  prevMonth	: function () {
    this._maybeRedrawMonth(this._prevMonthYear());
  },
  _maybeRedrawMonth : function(a_new) {
    var _newMon = a_new[0];
    var _newYear = a_new[1];
    if (!this._dateFilter.validMonthP ||
	this._dateFilter.validMonthP(_newYear, _newMon)) {
      this._currentMonth	= _newMon;
      this._currentYear 	= _newYear;
      this._redrawCalendar();
    }
  },
  _redrawCalendar	: function () {
    this._setLocaleHdr(); this._buildCalendar();
  },
  _setLocaleHdr	: function () {
    /* next link */
    var a_next	= this._nextMonthYear();
    $(this._id_datepicker_next).setAttribute('title',
					     this.getMonthLocale(a_next[0])+' '+a_next[1]);
    /* prev link */
    var a_prev	= this._prevMonthYear();
    $(this._id_datepicker_prev).setAttribute('title',
					     this.getMonthLocale(a_prev[0])+' '+a_prev[1]);
    /* header */
    $(this._id_datepicker_hdr).update('&nbsp;&nbsp;&nbsp;'+this.getMonthLocale(this._currentMonth)+'&nbsp;'+this._currentYear+'&nbsp;&nbsp;&nbsp;');
  }
};
