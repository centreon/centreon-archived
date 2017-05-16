/* global jQuery, navigator, centreonSelect2Locales */
(function ($) {
  function CentreonSelect2(settings, $elem) {
    this.internal = new CentreonSelect2Internal(settings, $elem);
  }

  function CentreonSelect2Internal(settings, $elem) {
    this.settings = settings;
    this.$elem = $elem;
    this.parent = $elem.parent();
    this.events = {
      shiftFirstEl: null
    };
    this.niceScroll = null;
    this.locale = 'en';
    this.messages = {};
    this.confirmBox = null;
    this.remoteData = false;
    this.extendedAction = false;
    this.savedSearch = '';
    this.ajaxOptions = {};

    /* Get if multiple */
    if (settings.multiple || $elem.attr('multiple')) {
      this.settings.multiple = true;
    }

    this.init();
  }

  CentreonSelect2Internal.prototype = {
    /**
     * Initialize select2
     */
    init: function () {
      var self = this;
      this.select2Options = this.settings.select2;

      this.initLocale();
      this.initAjax();

      /* Template for result display */
      this.select2Options.templateResult = function (item) {
        var text = item.text;
        var $result;
        if (self.settings.templateResult !== null) {
          text = self.settings.templateResult(item);
        }
        if (item.id) {
          $result = $('<div>')
              .data('did', item.id)
              .attr('title', item.text);
          if (typeof text === 'string') {
            return $result.text(text);
          }
          return $result.append(text);
        }
        return text;
      };
      /* Template for selection */
      this.select2Options.templateSelection = function (data, container) {
        if (data.hasOwnProperty('element') && data.element.hidden) {
          $(container).hide();
        }
        return $('<span>')
            .addClass('select2-content')
            .attr('title', data.text)
            .text(data.text);
      };

      if (this.remoteData) {
        this.select2Options.ajax = this.ajaxOptions;
      }

      this.$elem.select2(this.select2Options);

      this.initNiceScroll();
      this.initSaveSearch();
      this.initEvents();

      if (this.settings.allowClear) {
        this.initAllowClear();
      }
      if (this.settings.multiple) {
        this.initMultiple();
      }

      this.resizeSelect2();
    },

    resizeSelect2: function() {
      var formSpan = jQuery(".formTable span.select2-container");
      formSpan.css({
        'min-width': '360px',
      });
      formSpan.find('.select2-selection--multiple .select2-selection__rendered').css({
        'resize': 'vertical'
      });
    },
    /**
     * Load the locale, if not defined in settings use the browser locale
     */
    initLocale: function () {
      if (this.settings.locale !== null) {
        this.locale = this.settings.locale;
      } else {
        this.locale = navigator.language || navigator.userLanguage;
      }

      if (typeof centreonSelect2Locales !== 'undefined' &&
          centreonSelect2Locales.hasOwnProperty(this.locale)) {
        this.messages = centreonSelect2Locales[this.locale];
      }
    },
    /**
     * Initialize the allow clear
     */
    initAllowClear: function () {
      var self = this;

      this.clearButton = $('<span>')
          .css('cursor', 'pointer')
          .addClass('clearAllSelect2')
          .attr('title', this.translate('Clear field'))
          .append(
              $('<img>')
                  .attr('src', './img/icons/circle-cross.png')
                  .addClass('ico-14')
          );

      this.clearButton.on('click', function () {
        var currentValues = self.$elem.val();
        self.$elem.val('');
        if (self.remoteData) {
          self.$elem.empty().append($('<option>'));
        }
        self.$elem.trigger('change', currentValues);
      });

      $(this.parent).append(this.clearButton);
    },
    /**
     * Initialize the multiple
     */
    initMultiple: function() {
      var self = this;

      self.initShiftKey();
      self.initSelectAll();

      /* Add event for command on MacOSX */
      self.$elem.on('select2:closing', function (e) {
        if (e.params.hasOwnProperty('args') &&
            e.params.args !== undefined &&
            e.params.args.hasOwnProperty('originalEvent') &&
            e.params.args.originalEvent !== undefined &&
            e.params.args.originalEvent.metaKey) {
          e.preventDefault();
        }
      });
    },
    /**
     * Initialize the event for catch shift key for use multiple select
     */
    initShiftKey: function () {
      var self = this;

      /* Event on open select2 */
      self.$elem.on('select2:open', function (e) {
        e.preventDefault();
        self.events.shiftFirstEl = null;
      });

      /* Event when select an element with shift pressed */
      self.$elem.on('select2:selecting', function (e) {
        var endSelection = 0;
        var selectedValues = [];

        if (e.params.hasOwnProperty('args') &&
            e.params.args !== undefined &&
            e.params.args.hasOwnProperty('originalEvent') &&
            e.params.args.originalEvent !== undefined &&
            e.params.args.originalEvent.shiftKey) {
          e.preventDefault();

          /* The not element already selected */
          if (self.events.shiftFirstEl === null) {
            self.events.shiftFirstEl = e.params.args.data.id;
          } else {
            endSelection = e.params.args.data.id;
            selectedValues = self.getShiftSelected(endSelection);

            self.selectElements(selectedValues);
            self.$elem.select2('close');
            self.events.shiftFirstEl = null;
          }
        }
      });

      /* Event when unselect an element with shift pressed */
      self.$elem.on('select2:unselecting', function (e) {
        var endSelection = 0;
        var selectedValues = [];

        if (e.params.args.hasOwnProperty('originalEvent') &&
            e.params.args.originalEvent !== undefined &&
            e.params.args.originalEvent.shiftKey) {
          e.preventDefault();

          /* The not element already selected */
          if (self.events.shiftFirstEl === null) {
            self.events.shiftFirstEl = e.params.args.data.id;
          } else {
            endSelection = e.params.args.data.id;
            selectedValues = self.getShiftSelected(endSelection);

            self.unselectElements(selectedValues);
            self.$elem.select2('close');
            self.events.shiftFirstEl = null;
          }
        }
      });
    },
    /**
     * Initialize select all button when use multiple select option
     *
     * @todo add event for no remote select2 for count number of elements
     */
    initSelectAll: function() {
      var self = this;

      self.$elem.on('select2:open', function () {
        if ($('.select2-results-header').length === 0) {
          var $buttonSelectAll = $('<button>')
              .addClass('btc bt_info')
              .text(self.translate('Select all'));
          var $buttonSelectAllHeader;

          self.$totalElements = $('<span>')
              .addClass('select2-results-header__nb-elements-value');
          $buttonSelectAllHeader = $('<div>')
              .addClass('select2-results-header')
              .append(
                  $('<div>')
                      .addClass('select2-results-header__nb-elements')
                      .text(' ' + self.translate('element(s) found'))
                      .prepend(self.$totalElements)
              )
              .append(
                  $('<div>')
                      .addClass('select2-results-header__select-all')
                      .append($buttonSelectAll)
              );

          $('span.select2-results')
              .parents('.select2-dropdown')
              .prepend($buttonSelectAllHeader);

          $buttonSelectAll.on('click', function () {
            self.confirmSelectAll();
          });
        }
      });
    },
    /**
     * Initialize the nice scroll when opening select2
     */
    initNiceScroll: function () {
      var self = this;

      self.$elem.on('select2:open', function () {
        $('ul.select2-results__options').off('mousewheel');
        $('ul.select2-results__options').niceScroll({
          cursorcolor: '#818285',
          cursoropacitymax: 0.6,
          cursorwidth: 3,
          horizrailenabled: true,
          zindex: 5000,
          autohidemode: false
        });
      });
    },
    /**
     * Initialize geneal events
     */
    initEvents: function () {
      var self = this;

      /* Prevent closing when advanced event is running */
      this.$elem.on('select2:closing', function (e) {
        if (self.extendedAction) {
          e.preventDefault();
        }
      });
    },
    /**
     * Initialize the event for save and restore the search
     */
    initSaveSearch: function () {
      var self = this;

      /* Save the current search */
      this.$elem.on('select2:closing', function (e) {
        self.savedSearch = self.$elem.data()
            .select2.$container.find(".select2-search__field")
            .val();
      });

      this.$elem.on('select2:open', function (e) {
        if (self.savedSearch) {
          self.$elem.data()
              .select2.$container.find(".select2-search__field")
              .val(self.savedSearch);
          /* Wait for select2 finish to open */
          setTimeout(function () {
            self.$elem.data().select2.trigger(
                'query',
                {
                  term: self.savedSearch
                }
            );
          }, 10);
        }
      });
    },
    /**
     * Initialize ajax options and if using ajax
     */
    initAjax: function () {
      var self = this;

      if (self.settings.select2.hasOwnProperty('ajax') &&
          self.settings.select2.ajax.hasOwnProperty('url')) {
        self.remoteData = true;
        self.ajaxOptions = self.settings.select2.ajax;
        self.ajaxOptions.data = function (params) {
          return self.ajaxData(params);
        };
        self.ajaxOptions.processResults = function (data, params) {
          params.page = params.page || 1;
          if (self.settings.multiple) {
            self.$totalElements.text(data.total);
          }
          return {
            results: data.items,
            pagination: {
              more: (params.page * self.settings.pageLimit) < data.total
            }
          };
        }
      }
    },
    /**
     * Action when confirm select all
     */
    confirmSelectAll: function () {
      var self = this;
      var $validButton = $('<button>')
          .addClass('btc bt_success')
          .attr('type', 'button')
          .text(this.translate('Ok'));
      var $cancelButton = $('<button>')
          .addClass('btc bt_default')
          .attr('type', 'button')
          .text(this.translate('Cancel'));
      var $closeButton = $('<a>')
          .addClass('close')
          .css('cursor', 'pointer')
          .append('<img src="./img/icons/circle-cross.png" class="ico-18">');

      if (self.confirmBox !== null) {
        return;
      }

      self.extendedAction = true;

      /* Open popin */
      self.confirmBox = $('<div>')
          .append(
              $('<p>').
              text(
                  self.translate(
                      'Add {0} elements to selection ?',
                      self.$totalElements.text()
                  )
              )
          )
          .append(
              $('<div>')
                  .addClass('button_group_center')
                  .append($validButton)
                  .append(" ")
                  .append($cancelButton)
          )
          .append($closeButton)
      ;

      /* Add event on cancel button */
      $cancelButton.on('click', function() {
        self.closeSelectAllBox();
      });

      /* Add event on close button */
      $closeButton.on('click', function() {
        self.closeSelectAllBox();
      });

      /* Add event on confirm */
      $validButton.on('click', function () {
        self.selectAll();
      });

      /* Add event on click on overlay */
      $('#centreonPopinOverlay').on('click', function (e) {
        if ($(e.target).parents('.centreon-popin').length === 0) {
          self.closeSelectAllBox();
        }
      });

      /* Add event on esc key */
      $(document).on('keyup.centreonPopin', function (e) {
        if (e.keyCode === 27) {
          self.closeSelectAllBox();
        }
      });

      /* Open the popin */
      self.confirmBox.centreonPopin(
          {
            open: true,
            onClose: function () {
              self.confirmBox.remove();
              self.confirmBox = null;
            }
          }
      );
    },
    /**
     * Close the confirm box
     */
    closeSelectAllBox: function () {
      this.extendedAction = false;
      $(document).unbind('keyup.centreonPopin');
      if (this.confirmBox === null) {
        return;
      }
      this.confirmBox.centreonPopin('close');
    },
    /**
     * Select all elements matching to the search
     */
    selectAll: function () {
      var self = this;
      var search = this.$elem.data()
          .select2.$container.find('.select2-search__field')
          .val();
          var data = self.ajaxData({
               term: search
          });
      var selectedElements = [];
      var matchExp = new RegExp('.*' + search + '.*', 'i');
      delete data.page_limit;
      delete data.page;

      if (this.remoteData) {
        /* Execute select all for ajax */
        $.ajax({
          url: self.settings.select2.ajax.url,
          data: data,
          success: function (data) {
            var selectedValues = [];
            var selectedElements = [];
            var i = 0;

            /* Get already selected in DOM to avoid to select twice */
            self.$elem.find('option').each(function (idx, element) {
              var value = $(element).val();
              if (value.trim() !== '') {
                selectedValues.push(value);
              }
            });

            /* Prepare new items to add */
            for (i = 0; i < data.items.length; i++) {
              if (selectedValues.indexOf('' + data.items[i].id) < 0) {
                selectedElements.push(data.items[i]);
              }
            }
            self.selectElements(selectedElements);
            self.closeSelectAllBox();
            self.$elem.select2('close');
          }
        });
      } else {
        /* Execute select all for static */
        self.$elem.find('option').each(function (idx, element) {
          if ($(element).val().match(matchExp)) {
            selectedElements.push({
              id: $(element).val(),
              text: $(element).text()
            });
          }
        });
        self.selectElements(selectedElements);
        self.closeSelectAllBox();
        self.$elem.select2('close');
      }
    },
    /**
     * Select a list of elements
     *
     * @param {Array[Object]} elements - The list of elements
     * @param {String} elements.id - The value of the element
     * @param {String} elements.text - The display test of the element
     */
    selectElements: function (elements) {
      var self = this;
      var item;
      var option;
      var selectedElements;

      if (this.remoteData) {
        /* Append new elements */
        for (var i = 0; i < elements.length; i++) {
          item = elements[i];

          /* Create DOM option that is pre-selected by default */
          option = '<option selected value="' + item.id + '"';
          if (item.hide === true) {
            option += ' hidden';
          }
          option += '>' + item.text + '</option>';

          /* Append it to select */
          self.$elem.append(option);
        }
      } else {
        /* Select existing elements */
        selectedElements = elements.map(function (object) {
          return object.id;
        });
        self.$elem.val(selectedElements);
      }
      self.$elem.trigger('change');
    },
    /**
     * Select a list of elements
     *
     * @param {Array[Object]} elements - The list of elements
     * @param {String} elements.id - The value of the element
     * @param {String} elements.text - The display test of the element
     */
    unselectElements: function (elements) {
      var self = this;
      var item;
      var option;
      var selectedElements;
      var currentValues;
      var tmpIds;

      if (this.remoteData) {
        /* Remove elements */
        tmpIds = elements.map(function (object) {
          return object.id;
        });
        self.$elem.find('option').each(function (idx, element) {
          if (tmpIds.indexOf($(element).val()) >= 0) {
            $(element).remove();
          }
        });
      } else {
        /* Select existing elements */
        currentValues = self.$elem.val();
        tmpIds = elements.map(function (object) {
          return object.id;
        });
        selectedElements = currentValues.filter(function (id) {
          if (tmpIds.indexOf(id) >= 0) {
            return true;
          }
          return false;
        });
        self.$elem.val(selectedElements);
      }
      self.$elem.trigger('change');
    },
    /**
     * Get the elements selected by shift
     *
     * @param {String} endSelection - The id of mouse clicked
     * @return {Array[Object]} - The list of elements selected
     */
    getShiftSelected: function (endSelection) {
      var self = this;
      var startIndex = 0;
      var endIndex = 0;
      var tempIndex;
      var startSelection = self.events.shiftFirstEl;
      var selectedValues = [];

      $('.select2-results li>div').each(function (index) {
        var $this = $(this);
        if ($this.data('did') == startSelection) {
          startIndex = index;
        }
        if ($this.data('did') == endSelection) {
          endIndex = index;
        }
      });

      /* Good order */
      if (endIndex < startIndex) {
        tempIndex = startIndex;
        startIndex = endIndex;
        endIndex = tempIndex;
      }

      $(".select2-results li>div").each(function (index){
        var $this = $(this);

        if (index >= startIndex && index <= endIndex) {
          selectedValues.push(
              {
                id: $this.data('did').toString(),
                text: $this.text()
              }
          );
        }
      });

      return selectedValues;
    },
    /**
     * Prepare the data for ajax query
     */
    ajaxData: function (params) {
      var filterKey;
      var value;
      var data = {
        q: params.term,
        page_limit: this.settings.pageLimit,
        page: params.page || 1
      };

      for (filterKey in this.settings.additionnalFilters) {
        if (this.settings.additionnalFilters.hasOwnProperty(filterKey)) {
          if (typeof this.settings.additionnalFilters[filterKey] === 'string') {
            value = $(this.settings.additionnalFilters[filterKey]).val();
          } else {
            value = this.settings.additionnalFilters[filterKey]();
          }
          if (value !== null && value !== undefined && value !== '') {
            data[filterKey] = value;
          }
        }
      }

      return data;
    },
    /**
     * Format a string
     *
     * '{0} {1}' (first, second)
     * => first second
     */
    stringFormat: function (format) {
      var args = Array.prototype.slice.call(arguments, 1);
      return format.replace(/{(\d+)}/g, function (match, number) {
        if (typeof args[number] !== 'undefined') {
          return args[number];
        }
        return match;
      });
    },
    /**
     * Translate a string
     */
    translate: function (message) {
      var parameters = Array.prototype.slice.call(arguments, 1);
      if (this.messages.hasOwnProperty(message)) {
        return this.stringFormat(this.messages[message], parameters);
      }
      return this.stringFormat(message, parameters);
    }
  };

  CentreonSelect2.prototype = {
    /**
     * Action add nice scroll
     */
    addNiceScroll: function () {
      this.internal.niceScroll = this.internal.$elem.next('.select2-container')
          .find('ul.select2-selection__rendered')
          .niceScroll(
              {
                cursorcolor: '#818285',
                cursoropacitymax: 0.6,
                cursorwidth: 3,
                horizrailenabled: true,
                autohidemode: true
              }
          );
    },
    /**
     * Action remove nice scroll
     */
    removeNiceScroll: function () {
      this.internal.niceScroll.remove();
    },
    /**
     * Destroy the element
     */
    destroy: function () {
      this.internal.$elem.select2('destroy');
      this.internal.$elem.removeData('centreonSelect2');
    },
    /**
     * Update select2 settings
     *
     * @param {Object} settings - New settings, only differentials
     */
    updateSettings: function (settings) {
      this.internal.select2Options = $.extend(
        {},
        this.internal.select2Options,
        settings
      );
      this.internal.$elem.select2('destroy');
      this.internal.$elem.select2(this.internal.select2Options);
    }
  };

  $.fn.centreonSelect2 = function (options) {

    var args = Array.prototype.slice.call(arguments, 1);
    var settings = $.extend({}, $.fn.centreonSelect2.defaults, options);
    var methodReturn;
    var $set = this.each(function () {
      var $this = $(this);
      var data = $this.data("centreonSelect2");

      if (!data) {
        $this.data("centreonSelect2", ( data = new CentreonSelect2(settings, $this)));
        data.addNiceScroll();
      }

      if (typeof options === "string") {
        methodReturn = data[options].apply(data, args);
      }
    });

    return (methodReturn === undefined) ? $set : methodReturn;
  };

  $.fn.centreonSelect2.defaults = {
    allowClear: false,
    confirmMinNumber: 0,
    locale: null,
    templateResult: null,
    pageLimit: 20,
    additionnalFilters: {},
    select2: {
      allowClear: true
    }
  };
})(jQuery);

