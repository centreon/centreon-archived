/*==================================================
 *  Platform Utility Functions and Constants
 *==================================================
 */

Timeline.Platform.isIE = false;
Timeline.Platform.isWin = false;
Timeline.Platform.isWin32 = false;

(function() {
    Timeline.Platform.isIE= (navigator.appName.indexOf("Microsoft") != -1);
    
	var ua = navigator.userAgent.toLowerCase(); 
	Timeline.Platform.isWin = (ua.indexOf('win') != -1);
	Timeline.Platform.isWin32 = Timeline.Platform.isWin && (   
        ua.indexOf('95') != -1 || 
        ua.indexOf('98') != -1 || 
        ua.indexOf('nt') != -1 || 
        ua.indexOf('win32') != -1 || 
        ua.indexOf('32bit') != -1
    );
})();

Timeline.Platform.getDefaultLocale = function() {
    return Timeline.Platform.clientLocale;
};