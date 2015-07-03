/**
 * Utils for Centreon
 */

/**
 * Escape a string for present javascript injection
 */
String.prototype.escapeSecure = function () {
  var returnStr, tmpStr;
  /* Remove script tags */
  tmpStr = $(this);
  tmpStr.find("script").remove();
  returnStr = tmpStr.html();

  return returnStr;
};
