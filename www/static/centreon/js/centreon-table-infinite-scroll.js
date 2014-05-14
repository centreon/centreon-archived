/*global jQuery:false */
/**
 * Add infinite scroll for a table
 */
(function( $ ) {
  function CentreonTableInfiniteScroll( settings, $elem ) {
    var classes = [],
        $this = this,
        regexCls = /^span-\d{1,2}$/;
    this.settings = settings;
    this.$elem = $elem;
    this.lastTime = null;
    this.recentTime = null;
    this.loading = true;
    this.hasEvent = true;
    this.lastScroll = 0;
    this.newNotSee = 0;
    this.trHeading = "";

    /* Prepare templates */
    this.templateRows = null;
    if ( this.settings.templateRows !== "" ) {
      this.templateRows = Hogan.compile( this.settings.templateRows, { delimiters: "<% %>" } );
    }
    this.templateCols = {};
    if ( this.templateRows === null ) {
      $.each( this.settings.templateCols, function( idx, tpl ) {
        $this.templateCols[ idx ] = Hogan.compile( tpl , { delimiters: "<% %>" } );
      });
    }
    
    this.$elem.addClass( this.settings.cls );

    this.$badge = this.$elem.children( "thead" ).find( ".badge-new-events" );
    this.$badge.find( "a" ).on( "click" , function( e ) {
      e.preventDefault();
      $this.$elem.children( "tbody" ).scrollTop( 0 );
      $this.newNotSee = 0;
      $this.$badge.hide();
    });


    /* Load columns classes */
    $.each(this.$elem.find( "thead > tr > th" ), function( idx, th ) {
      var ret = $.grep($(th)[0].classList, function( item ) {
        return regexCls.test(item);
      });
      if ( ret[0] !== undefined ) {
        classes.push(ret[0]);
      }
    });
    this.classes = classes;

    /* Add event to scroll */
    this.$elem.children( "tbody" ).on( "scroll", function( e ) {
      if ( $this.hasEvent ) {
        if ( $( this ).scrollTop() === 0 ) {
          $this.newNotSee = 0;
          $this.$badge.hide();
        }
        if ( $this.lastScroll < $( this ).scrollTop() ) {
          if ( $( this ).scrollTop() + $this.$elem.children( "tbody" ).height() > $this.trHeading * $this.$elem.find( "tbody > tr ").length - $this.trHeading ) {
            $this.loadData();
          }
        }
      }
      $this.lastScroll = $( this ).scrollTop();
    });

    /* Add event when filters change */
    if ( this.settings.formFilter !== "" ) {
      $( this.settings.formFilter ).on( "change", function( e ) {
        $this.$elem.children( "tbody" ).text( "" );
        $this.lastTime = null;
        $this.recentTime = null;
        $this.loading = true;
        $this.hasEvent = true;
        $this.lastScroll = 0;
        $this.newNotSee = 0;
        $this.loadData();
      });
    }

    this.resize();
    this.loadData();
    setTimeout( function() { $this.loadNewData(); }, this.settings.refresh );
  }

  CentreonTableInfiniteScroll.prototype = {
    resize: function() {
      var $parent = this.$elem.parent(),
          $parentContent = this.$elem.parents( this.settings.parentSelector ),
          height = 0;

      /* Add height of all object in content */
      $.each($parentContent.children().not( $parent ), function( idx, $elem ) {
        height += $( $elem ).height();
      });
      /* Add height of all object in parent */
      $.each($parent.children().not( this.$elem ), function( idx, $elem ) {
        height += $( $elem ).height();
      });

      /* Add height of table thead */
      height += this.$elem.children( "thead" ).height();

      this.$elem.children( "tbody" ).height( $parentContent.height() - height );
    },

    preResize: function() {
      this.$elem.children( "tbody" ).height(0);
    },

    loadData: function() {
      var $this = this,
          data;
      if ( this.settings.ajaxUrlGetScroll === "" ) {
        return;
      }
      data = this.prepareData();
      data.startTime = this.lastTime;
      $.ajax({
        url: this.settings.ajaxUrlGetScroll,
        type: "POST",
        data: data,
        dataType: "json",
        success: function( data, statusText, jqXHR ) {
          if ( data.data.length === 0 ) {
            $this.hasEvent = false;
            $this.loading = false;
            $this.recentTime = new Date().getTime() / 1000; /* @todo better */
            return;
          }
          $.each( data.data, function( idx, values ) {
            var line;
            /* Insert with template */
            if ( $this.templateRows !== null ) {
              line = $this.templateRows.render( values );
              $this.$elem.children( "tbody" ).append( $( line ) );
            } else {
              /* Default insert line */
              var $tr = $( "<tr></tr>" ),
                  i = 0;
              $.each( values, function( key, value ) {
                if ( key in $this.templateCols ) {
                  value = $this.templateCols[ key ].render( values );
                }
                if ( i < $this.classes.length ) {
                  $( "<td></td>" )
                    .addClass( $this.classes[ i++ ] )
                    .html( value )
                    .appendTo( $tr );
                }
              });
              $tr.appendTo( $this.$elem.children( "tbody" ) );
            }
          });

          $this.lastTime = data.lastTimeEntry;
          if ( $this.recentTime === null ) {
            $this.recentTime = data.recentTime;
          }

          /* Fix for time is 0 */
          if ( data.data.length < $this.settings.limit ) {
            $this.loading = false;
          }

          /* Continu to load in first call */
          if ( $this.loading ) {
            if ( $this.trHeading === "" ) {
              $this.trHeading = $this.$elem.find( "tbody > tr" ).height();
            }
            if ( ( $this.trHeading * $this.$elem.find( "tbody > tr" ).length ) < $this.$elem.children( "tbody" ).height() ) {
              $this.loadData();
            } else {
              $this.loading = false;
            }
          }

          /* Send trigger for loaded data */
          if ( !$this.loading ) {
            $this.$elem.trigger( "loaded" );
          }
        },
        beforeSend: function( jqXHR, settings ) {
        },
        complete: function( jqXHR, statusText ) {
        }
      });
    },
    loadNewData: function() {
      var $this = this,
          data;
      if ( this.settings.ajaxUrlGetNew === "" ||
        this.recentTime === null ) {
        setTimeout( function() { $this.loadNewData(); }, $this.settings.refresh );
        return;
      }
      data = this.prepareData();
      data.startTime = this.recentTime;
      $.ajax({
        url: this.settings.ajaxUrlGetNew,
        type: "POST",
        data: data,
        dataType: "json",
        success: function( data, statusText, jqXHR ) {
          var nbEl = data.data.length - 1,
              i, $tr;
          for ( ; nbEl >= 0; nbEl--) {
            values = data.data[nbEl];
            $tr = $( "<tr></tr>" );
            i = 0;
            $.each( values, function( key, value ) {
              $( "<td></td>" )
                .addClass( $this.classes[ i++ ] )
                .text( value )
                .appendTo( $tr );
            });
            $tr.prependTo( $this.$elem.children( "tbody" ) );
          }

          if ( data.data.length > 0 ) {
            if ( $this.$elem.children( "tbody" ).scrollTop() !== 0 ) {
              $this.newNotSee += data.data.length;
              $this.$badge.find( 'span' ).text( $this.newNotSee + " events" );
              $this.$badge.show();
            }
            $this.recentTime = data.recentTime;
          }
          setTimeout( function() { $this.loadNewData(); }, $this.settings.refresh );
          
          /* Send trigger for loaded data */
          if ( !$this.loading ) {
            $this.$elem.trigger( "loaded" );
          }
        }
      });
    },
    prepareData: function() {
      var data = {};
      /* Get filter form */
      if ( this.settings.formFilter !== "" ) {
        $.each( $( this.settings.formFilter ).serializeArray(), function( i, field ) {
          var tmpValue;
          if ( field.value !== "" ) {
            if ( field.name in data ) {
              if ( data[field.name] instanceof Array ) {
                data[field.name].push( field.value );
              } else {
                tmpValue = data[field.name];
                data[field.name] = [];
                data[field.name].push( tmpValue );
                data[field.name].push( field.value );
              }
            } else {
              data[field.name] = field.value;
            }
          }
        } );
      }
      return data;
    }
  };

  $.fn.centreonTableInfiniteScroll = function( options ) {
    var args = Array.prototype.slice.call( arguments, 1 ),
        settings = $.extend( {}, $.fn.centreonTableInfiniteScroll.defaults, options ),
        $set,
        methodReturn;

    $set = this.each(function() {
      var $this = $( this ),
          data = $this.data( "centreonTableInfiniteScroll" );

      if ( !data ) {
        $this.data( "centreonTableInfiniteScroll", ( data = new CentreonTableInfiniteScroll( settings, $this ) ) );
      }
      if ( typeof options === "string" ) {
        methodReturn = data[ options ].apply( data, args );
      }

      return ( methodReturn === undefined ) ? $set : methodReturn;
    });
  };

  $.fn.centreonTableInfiniteScroll.defaults = {
    cls: "table-infinitescroll",
    parentSelector: ".content",
    ajaxUrlGetScroll: "",
    ajaxUrlGetNew: "",
    refresh: 10000,
    limit: 20,
    formFilter: "",
    templateRows: "",
    templateCols: {}
  };
})( jQuery );
