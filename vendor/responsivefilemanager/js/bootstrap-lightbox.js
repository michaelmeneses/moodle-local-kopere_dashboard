!function ( e ) {
    "use strict";
    var t                     = function ( t, n ) {
        this.options  = n;
        this.$element = e ( t ).delegate ( '[data-dismiss="lightbox"]', "click.dismiss.lightbox", e.proxy ( this.hide, this ) );
        this.options.remote && this.$element.find ( ".lightbox-body" ).load ( this.options.remote )
    };
    t.prototype               = e.extend ( {}, e.fn.modal.Constructor.prototype );
    t.prototype.constructor   = t;
    t.prototype.enforceFocus  = function () {
        var t = this;
        e ( document ).on ( "focusin.lightbox", function ( e ) {
            if ( t.$element[ 0 ] !== e.target && !t.$element.has ( e.target ).length ) {
                t.$element.focus ()
            }
        } )
    };
    t.prototype.show          = function () {
        var t = this, n = e.Event ( "show" );
        this.$element.trigger ( n );
        if ( this.isShown || n.isDefaultPrevented () )return;
        this.isShown = true;
        this.escape ();
        this.preloadSize ( function () {
            t.backdrop ( function () {
                var n = e.support.transition && t.$element.hasClass ( "fade" );
                if ( !t.$element.parent ().length ) {
                    t.$element.appendTo ( document.body )
                }
                hide_animation ();
                t.$element.show ();
                if ( n ) {
                    t.$element[ 0 ].offsetWidth
                }
                t.$element.addClass ( "in" ).attr ( "aria-hidden", false );
                t.enforceFocus ();
                n ? t.$element.one ( e.support.transition.end, function () {
                    t.$element.focus ().trigger ( "shown" )
                } ) : t.$element.focus ().trigger ( "shown" )
            } )
        } )
    };
    t.prototype.hide          = function ( t ) {
        t && t.preventDefault ();
        var n = this;
        t     = e.Event ( "hide" );
        this.$element.trigger ( t );
        if ( !this.isShown || t.isDefaultPrevented () )return;
        this.isShown = false;
        this.escape ();
        e ( document ).off ( "focusin.lightbox" );
        this.$element.removeClass ( "in" ).attr ( "aria-hidden", true );
        e.support.transition && this.$element.hasClass ( "fade" ) ? this.hideWithTransition () : this.hideModal ()
    };
    t.prototype.escape        = function () {
        var e = this;
        if ( this.isShown && this.options.keyboard ) {
            this.$element.on ( "keyup.dismiss.lightbox", function ( t ) {
                t.which == 27 && e.hide ()
            } )
        } else if ( !this.isShown ) {
            this.$element.off ( "keyup.dismiss.lightbox" )
        }
    };
    t.prototype.preloadSize   = function ( t ) {
        var n = e.Callbacks ();
        if ( t )n.add ( t );
        var r    = this;
        var i, s, o, u, a, f, l, c, h, p;
        i        = e ( window ).height ();
        s        = e ( window ).width ();
        o        = parseInt ( r.$element.find ( ".lightbox-content" ).css ( "padding-top" ), 10 );
        u        = parseInt ( r.$element.find ( ".lightbox-content" ).css ( "padding-bottom" ), 10 );
        a        = parseInt ( r.$element.find ( ".lightbox-content" ).css ( "padding-left" ), 10 );
        f        = parseInt ( r.$element.find ( ".lightbox-content" ).css ( "padding-right" ), 10 );
        l        = r.$element.find ( ".lightbox-content" ).find ( "img:first" );
        c        = new Image;
        c.onload = function () {
            if ( c.width + a + f >= s ) {
                h        = c.width;
                p        = c.height;
                c.width  = s - a - f;
                c.height = p / h * c.width
            }
            if ( c.height + o + u >= i ) {
                h        = c.width;
                p        = c.height;
                c.height = i - o - u;
                c.width  = h / p * c.height
            }
            r.$element.css ( {
                position      : "fixed",
                width         : c.width + a + f,
                height        : c.height + o + u,
                top           : i / 2 - (c.height + o + u) / 2,
                left          : "50%",
                "margin-left" : -1 * (c.width + a + f) / 2
            } );
            r.$element.find ( ".lightbox-content" ).css ( { width : c.width, height : c.height } );
            n.fire ()
        };
        c.src    = l.attr ( "src" )
    };
    var n                     = e.fn.lightbox;
    e.fn.lightbox             = function ( n ) {
        return this.each ( function () {
            var r = e ( this );
            var i = r.data ( "lightbox" );
            var s = e.extend ( {}, e.fn.lightbox.defaults, r.data (), typeof n == "object" && n );
            if ( !i )r.data ( "lightbox", i = new t ( this, s ) );
            if ( typeof n == "string" )i[ n ] (); else if ( s.show )i.show ()
        } )
    };
    e.fn.lightbox.defaults    = { backdrop : true, keyboard : true, show : true };
    e.fn.lightbox.Constructor = t;
    e.fn.lightbox.noConflict  = function () {
        e.fn.lightbox = n;
        return this
    };
    e ( document ).on ( "click.lightbox.data-api", '[data-toggle="lightbox"]', function ( t ) {
        var n = e ( this );
        var r = n.attr ( "href" );
        var i = e ( n.attr ( "data-target" ) || r && r.replace ( /.*(?=#[^\s]+$)/, "" ) );
        var s = i.data ( "lightbox" ) ? "toggle" : e.extend ( { remote : !/#/.test ( r ) && r }, i.data (), n.data () );
        t.preventDefault ();
        i.lightbox ( s ).one ( "hide", function () {
            n.focus ()
        } )
    } )
} ( window.jQuery )