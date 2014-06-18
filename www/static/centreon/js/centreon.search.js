( function( $ ) {
  $.fn.centreonsearch = function( options ) {
    return this.each( function() {
      var $this = $( this );
      var opts = $.extend( {}, $.fn.centreonsearch.defaults, options );
      $this.data( "centreonsearch", new $.CentreonSearch(
          $this,
          $.meta ? $.extend( {}, opts, $this.data() ) : opts
      ));
    });
  };

  $.fn.centreonsearch.defaults = {
    minChars: 3
  };

  $.CentreonSearch = function( $elem, options ) {
    var self = this;
    this.options = options;

    this.tags = this.options.tags;

    this.active = false;

    this.currentTag = null;

    this.currentList = [];

    this.mousedover = false;
    
    this.dom = {};

    this.dom.$elem = $elem;

    this.dom.$elem.attr( "autocomplete", "off" );

    this.dom.$results = $( "<ul></ul>" ).addClass( "typeahead" ).addClass( "dropdown-menu" ).css({
      position: "absolute"
    });
    /* Resize for input size */
    this.resize();
    $( "body" ).append( this.dom.$results );

    /* Bind event listener */
    $elem.on( "keyup" , $.proxy( self.switchAction, self ) );
    $elem.on( "blur", $.proxy( self.blur, self ) );
    this.dom.$results.on( "click", "li", function( e ) {
      self.valid( $( e.currentTarget ) );
    });
    this.dom.$results.on( "mouseover", "li", function( e ) {
      self.mousedover = true;
      self.dom.$results.find( ".active" ).removeClass( "active" );
      $( e.currentTarget ).addClass( "active" );
    });
    this.dom.$results.on( "mouseout", "li", function( e ) {
      self.mousedover = false;
      $( e.currentTarget ).removeClass( "active" );
    });
    $( window ).resize( $.proxy( self.resize, self ) );
  };

  $.CentreonSearch.prototype.switchAction = function( e ) {
    var self = this;
    switch ( e.keyCode ) {
      case 35: // end
      case 36: // home
      case 16: // shift
      case 17: // ctrl
      case 18: // alt
      case 37: // left
      case 39: // right
        break;

      case 38: // up
        e.preventDefault();
        if ( this.active ) {
          this.focusPrev();
        }
        break;

      case 40: // down
        e.preventDefault();
        if ( this.active ) {
          this.focusNext();
        }
        break;

      case 13: // enter
        this.valid();
        break;

      case 27: // escape
        this.cleanChoises();
        break;

      default:
        this.search();
        break;
    }
  };

  $.CentreonSearch.prototype.search = function() {
    var self = this;
    var input = this.dom.$elem;
    this.currentList = $( input ).val().split( " " );
    var lastElement = this.currentList[ this.currentList.length - 1 ];
    /* Action only if more than 'minChars' characters */
    if ( lastElement.length >= this.options.minChars ) {
       var sepPos = lastElement.indexOf( ":" );
       /* Search for tags */
       if ( sepPos == -1 ) {
         this.currentTag = null;
         var valid = Object.keys( this.tags ).filter( function( el ) {
           if ( el.substring( 0, lastElement.length ) == lastElement ) {
             return el;
           }
         });
         if ( valid.length > 0 ) {
           this.displayChoises( valid );
         } else {
           this.cleanChoises(); 
         }
       /* Search for informations */
       } else {
         this.cleanChoises();
         var typeSearch = lastElement.substring( 0, sepPos );
         this.currentTag = typeSearch;
         var searchStr = lastElement.substring( sepPos + 1 );
         var listChoises = [];
         if ( $.inArray( typeSearch, Object.keys( this.tags ) ) != -1 &&
           searchStr.length >= this.options.minChars ) {
           if ( typeof( this.tags[ typeSearch ] ) == "function" ) {
             /* Is a function */
             listChoises = this.tags[ typeSearch ]( searchStr );
           } else if ( typeof( this.tags[ typeSearch ] ) == "string" ) {
             /* Is a select */
             var children = $( this.tags[ typeSearch ] ).children( "option" );
             var tmpList = children.filter( function( el, list ) {
               var tmpStr = $( list ).val();
               if ( tmpStr.substring( 0, searchStr.length ).toLowerCase() == searchStr.toLowerCase() ) {
                 return true;
               }
             });
             var i = 0;
             for ( i; i < tmpList.length; i++ ) {
               if ( typeof( $( tmpList[ i ] ).val() ) == "string") {
                 listChoises.push( $( tmpList[ i ] ).val() );
               }
             }
           } else if ( typeof( this.tags[ typeSearch ] ) == "object" ) {
             /* Is a object : array */
             listChoises = this.tags[ typeSearch ].filter( function( e l) {
               if ( el.substring( 0, searchStr.length ).toLowerCase() == searchStr.toLowerCase() ) {
                 return true;
               }
             });
           }
           if ( listChoises.length > 0 ) {
             this.displayChoises( listChoises );
           }
         }
       }
    } else {
      this.cleanChoises();
    }
  };
  
  $.CentreonSearch.prototype.displayChoises = function( list ) {
    this.active = true;
    var self = this;
    this.dom.$results.html( "" );
    $.each( list, function( idx, value ) {
      var $li = $( "<li></li>" ).css( "cursor", "pointer" );
      $( "<a></a>" ).text( value ).appendTo( $li );
      $li.appendTo( self.dom.$results );
    });
    this.dom.$results.show();
    this.position();
  };

  $.CentreonSearch.prototype.cleanChoises = function() {
    this.dom.$results.hide();
    this.dom.$results.html( "" );
    this.active = false;
  };

  $.CentreonSearch.prototype.position = function() {
    var offset = this.dom.$elem.offset();
    var height = this.dom.$results.outerHeight();
    var totalHeight = $( window ).outerHeight();
    var inputBottom = offset.top + this.dom.$elem.outerHeight();
    var bottomIfDown = inputBottom + height;
    var position = {
      top: inputBottom,
      left: offset.left
    };
    if ( bottomIfDown > totalHeight ) {
      var topIfUp = offset.top - height;
      if ( topIfUp >= 0 ) {
        position.top = topIfUp;
      }
    }
    this.dom.$results.css( position );
  };

  $.CentreonSearch.prototype.focusNext = function() {
    var el = this.dom.$results.children( ".active" );
    if ( el.length > 0 ) {
      var newActive = $( el ).next( "li" );
      $( el ).removeClass( "active" );
      if ( newActive.length === 0 ) {
        this.dom.$results.children( ":first" ).addClass( "active" );  
      } else {
        newActive.addClass( "active" );
      }
    } else {
      this.dom.$results.children( ":first" ).addClass( "active" );
    }
  };

  $.CentreonSearch.prototype.focusPrev = function() {
    var el = this.dom.$results.children( ".active" );
    if ( el.length > 0 ) {
      var newActive = $( el ).prev( "li" );
      $( el ).removeClass( "active" );
      if ( newActive.length === 0 ) {
        this.dom.$results.children( ":last" ).addClass( "active" );
      } else {
        newActive.addClass( "active" );
      }
    } else {
      this.dom.$results.children( ":last" ).addClass( "active" );
    }
  };

  $.CentreonSearch.prototype.valid = function ( el ) {
    var activeEl;
    if ( typeof( el ) == "undefined" ) {
      activeEl = this.dom.$results.children( ".active" );
    } else {
      activeEl = el;
    }
    if ( activeEl.length > 0 ) {
      var concat = activeEl.children( "a" ).text();
      if ( this.currentTag !== null ) {
        concat = this.currentTag + ":" + concat;
      } else {
        concat += ":";
      }
      this.currentList[ this.currentList.length - 1 ] = concat;
      this.dom.$elem.val( this.currentList.join( " " ) );
      this.cleanChoises();
    } else {
      console.log( "action filter" );
    }
  };

  $.CentreonSearch.prototype.resize = function() {
    this.dom.$results.width( this.dom.$elem.width() );
  };

  $.CentreonSearch.prototype.blur = function() {
    if ( !this.mousedover ) {
      this.cleanChoises();
    }
  };
})( jQuery );
