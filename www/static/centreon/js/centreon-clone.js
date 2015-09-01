/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

(function($) {
  function CentreonClone(settings, $elem) {
    var self = this;

    this.settings = settings;
    this.$elem = $elem;
    this.maxHeight = false;

    /* Count number of already in page */
    this.pos = this.$elem.find('.cloned_element').length;

    if (this.pos == 0) {
      this.pos = 1;
    }

    if ('addBtn' in this.settings) {
      this.$addBtn = $(this.settings.addBtn);
    } else {
      this.$addBtn = $('.addclone');
    }

    this.$template = $elem.find('.clone_template');

    /* Add event */
    this.$addBtn.on('click', function() {
      self.addElement();
      self.resizeHeight();
    });
    this.$elem.on('click', '.remove-trigger', function() {
      var $el = $(this).closest('li.cloned_element');
      self.removeElement($el);
    });
    this.$elem.on('change', 'input.hidden-value-trigger', function() {
      var $el = $(this).closest('li.cloned_element');
      self.changeElement($el);
    });

    this.resizeHeight();
  }

  CentreonClone.prototype = {
    addElement: function(values) {
      var self = this,
          $newEl = this.$template.clone().css('display', 'block')
                     .removeClass('clone_template')
                     .addClass('cloned_element')
                     .prependTo(this.$elem);

      if (typeof values !== 'undefined') {
        $.each(values, function(key, value) {
          $newEl.find(key).val(value);
        });
      }

      $newEl.find('input,textarea,select').each(function(idx, el) {
        $(el).attr('name', $(el).attr('name').replace('#index#', self.pos));
      });
      self.pos += 1;


      $.each(this.settings.events.add, function(idx, fct) {
        fct($newEl);
      });
    },
    removeElement: function($el) {
      $.each(this.settings.events.remove, function(idx, fct) {
        fct($el);
      });
      $el.remove();
    },
    changeElement: function($el) {
      /* @todo factorize */
      $.each(this.settings.events.change, function(idx, fct) {
        fct($el);
      });
    },
    resizeHeight: function() {

        var nbElem = this.settings.nbElementForScroll;

        this.height = this.$elem.find('.cloned_element').height();

      if (this.pos > 0 && this.pos > nbElem) {

          var countHeight = nbElem * this.height+'px';

          $(this.$elem).slimScroll({
              height: countHeight,
              railOpacity: 0.9
          });

       this.maxHeight = true;
      }
    }
  };

  $.fn.centreonClone = function(options) {
    var $set,
        args = Array.prototype.slice.call(arguments, 1),
        settings = $.extend({}, $.fn.centreonClone.defaults, options),
        methodReturn;

    $set = this.each(function() {
      var $this = $(this),
          data = $this.data('centreonClone');

      if (!data) {
        $this.data('centreonClone', (data = new CentreonClone(settings, $this)));
      }
      if (typeof options === 'string') {
        methodReturn = data[options].apply(data, args);
      }
    });

    return (methodReturn === undefined) ? $set : methodReturn;
  };

  $.fn.centreonClone.defaults = {
    name: null,
    nbElementForScroll: 5,
    events: {
      add: [],
      remove: [],
      change: []
    }
  };
})(jQuery);
