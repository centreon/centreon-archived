/*==================================================
 *  Timeline API
 *
 *  This file will load all the Javascript files
 *  necessary to make the standard timeline work.
 *  It also detects the default locale.
 *
 *  Include this file in your HTML file as follows:
 *
 *    <script src="http://simile.mit.edu.nyud.net:8080/timeline/api/scripts/timeline-api.js" type="text/javascript"></script>
 *
 *  Note that we are using Coral CND [1] to reduce
 *  the load on our server.
 *
 *  [1] http://coralcdn.org/
 *
 *==================================================
 */
 
var Timeline = new Object();
Timeline.Platform = new Object();


    /*
        HACK: We need these 2 things here because we cannot simply append
        a <script> element containing code that accesses Timeline.Platform
        to initialize it because IE executes that <script> code first
        before it loads timeline.js and util/platform.js.
    */

(function() {
    var javascriptFiles = [
        "timeline.js",
        
        "util/platform.js",
        "util/debug.js",
        "util/xmlhttp.js",
        "util/dom.js",
        "util/graphics.js",
        "util/date-time.js",
        "util/data-structure.js",
        
        "units.js",
        "themes.js",
        "ethers.js",
        "ether-painters.js",
        "labellers.js",
        "sources.js",
        "layouts.js",
        "painters.js",
        "decorators.js"
    ];
    var cssFiles = [
        "timeline.css",
        "ethers.css",
        "events.css"
    ];
    
    var localizedJavascriptFiles = [
        "labellers.js"
    ];
    var localizedCssFiles = [
    ];
    
    // ISO-639 language codes, ISO-3166 country codes (2 characters)
    var supportedLocales = [
        "en",       // English
        "es",       // Spanish
        "fr",       // French
        "it",       // Italian
        "ru",       // Russian
        "se",       // Swedish
        "vi",       // Vietnamese
        "zh"        // Chinese
    ];
    
    try {
        var desiredLocales = [ "en" ];
        var defaultServerLocale = "en";
        
        (function() {
            var heads = document.documentElement.getElementsByTagName("head");
            for (var h = 0; h < heads.length; h++) {
                var scripts = heads[h].getElementsByTagName("script");
                for (var s = 0; s < scripts.length; s++) {
                    var url = scripts[s].src;
                    var i = url.indexOf("timeline-api.js");
                    if (i >= 0) {
                        Timeline.urlPrefix = url.substr(0, i);
                        
                        // Parse parameters
                        var q = url.indexOf("?");
                        if (q > 0) {
                            var params = url.substr(q + 1).split("&");
                            for (var p = 0; p < params.length; p++) {
                                var pair = params[p].split("=");
                                if (pair[0] == "locales") {
                                    desiredLocales = desiredLocales.concat(pair[1].split(","));
                                } else if (pair[0] == "defaultLocale") {
                                    defaultServerLocale = pair[1];
                                }
                            }
                        }
                        
                        return;
                    }
                }
            }
            throw new Error("Failed to derive URL prefix for Timeline API code files");
        })();
        
        var includeJavascriptFile = function(filename) {
            document.write("<script src='" + Timeline.urlPrefix + "scripts/" + filename + "' type='text/javascript'></script>");
        };
        var includeCssFile = function(filename) {
            document.write("<link rel='stylesheet' href='" + Timeline.urlPrefix + "styles/" + filename + "' type='text/css'/>");
        }
        
        /*
         *  Include non-localized files
         */
        for (var i = 0; i < javascriptFiles.length; i++) {
            includeJavascriptFile(javascriptFiles[i]);
        }
        for (var i = 0; i < cssFiles.length; i++) {
            includeCssFile(cssFiles[i]);
        }
        
        /*
         *  Include localized files
         */
        var loadLocale = [];
        loadLocale[defaultServerLocale] = true;
        
        var tryExactLocale = function(locale) {
            for (var l = 0; l < supportedLocales.length; l++) {
                if (locale == supportedLocales[l]) {
                    loadLocale[locale] = true;
                    return true;
                }
            }
            return false;
        }
        var tryLocale = function(locale) {
            if (tryExactLocale(locale)) {
                return locale;
            }
            
            var dash = locale.indexOf("-");
            if (dash > 0 && tryExactLocale(locale.substr(0, dash))) {
                return locale.substr(0, dash);
            }
            
            return null;
        }
        
        for (var l = 0; l < desiredLocales.length; l++) {
            tryLocale(desiredLocales[l]);
        }
        
        var defaultClientLocale = defaultServerLocale;
        var defaultClientLocales = ("language" in navigator ? navigator.language : navigator.browserLanguage).split(";");
        for (var l = 0; l < defaultClientLocales.length; l++) {
            var locale = tryLocale(defaultClientLocales[l]);
            if (locale != null) {
                defaultClientLocale = locale;
                break;
            }
        }
        
        for (var l = 0; l < supportedLocales.length; l++) {
            var locale = supportedLocales[l];
            if (loadLocale[locale]) {
                for (var i = 0; i < localizedJavascriptFiles.length; i++) {
                    includeJavascriptFile("l10n/" + locale + "/" + localizedJavascriptFiles[i]);
                }
                for (var i = 0; i < localizedCssFiles.length; i++) {
                    includeCssFile("l10n/" + locale + "/" + localizedCssFiles[i]);
                }
            }
        }
        
        document.write(
            "<script type='text/javascript'>" +
                "Timeline.Platform.serverLocale = '" + defaultServerLocale + "';" + 
                "Timeline.Platform.clientLocale = '" + defaultClientLocale + "';" +
            "</script>"
        );
    } catch (e) {
        alert(e);
    }
})();