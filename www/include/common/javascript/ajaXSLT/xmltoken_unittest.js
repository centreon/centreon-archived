// Copyright 2006 Google Inc.
// All Rights Reserved
//
// Unit test for xmltoken.js.
//
// Author: Junji Takagi <jtakagi@google.com>

// Test if regexp matches the str and RegExp.exec returns exactly the match.
function assertOk(comment, regexp, str, match) {
  assertNotNull(comment, regexp.exec(str));
  assertEquals(comment, regexp.exec(str)[0], match);
  assertTrue(comment, regexp.test(str));
}

// Test if regexp doesn't match the str.
function assertNg(comment, regexp, str) {
  assertNull(comment, regexp.exec(str));
  assertFalse(comment, regexp.test(str));
}

// Concat chars in various way and test them with regexp.
function doTestXmlName(comment, regexp,
                     okFirstChars, ngFirstChars,
                     okSecondChars, ngSecondChars) {
  var okSecondString = okSecondChars.join('');
  for (var i = 0; i < okFirstChars.length; i++) {
    assertOk(comment + ' with ok #' + i, regexp,
             okFirstChars[i],
             okFirstChars[i]);
    assertOk(comment + ' with ok #' + i + ' + oks', regexp,
             okFirstChars[i] + okSecondString,
             okFirstChars[i] + okSecondString);
    for (var j = 0; j < okSecondChars.length; j++) {
      assertOk(comment + ' with ok #' + i + ' + ok #' + j, regexp,
               okFirstChars[i] + okSecondChars[j],
               okFirstChars[i] + okSecondChars[j]);
      assertOk(comment + ' with ok #' + i + ' + ok #' + j + ' + oks', regexp,
               okFirstChars[i] + okSecondChars[j] + okSecondString,
               okFirstChars[i] + okSecondChars[j] + okSecondString);
      var k = (i + j) % ngSecondChars.length;
      assertOk(comment + ' with ' +
               'ok #' + i + ' + ok #' + j + ' + ng #' + k, regexp,
               okFirstChars[i] + okSecondChars[j] + ngSecondChars[k],
               okFirstChars[i] + okSecondChars[j]);
    }
    var j = i % ngSecondChars.length;
    assertOk(comment + ' with ok #' + i + ' + ng #' + j, regexp,
             okFirstChars[i] + ngSecondChars[j],
             okFirstChars[i]);
    assertOk(comment + ' with ok #' + i + ' + oks + ng #' + j, regexp,
             okFirstChars[i] + okSecondString + ngSecondChars[j],
             okFirstChars[i] + okSecondString);
  }
  for (var i = 0; i < ngFirstChars.length; i++) {
    assertNg(comment + ' with ng #' + i, regexp,
             ngFirstChars[i]);
    // It doesn't make sense to test with ngFirstChars[i] + okSecondChars[j].
    for (var j = 0; j < ngSecondChars.length; j++) {
      assertNg(comment + ' with ng #' + i + ' + ng #' + j, regexp,
               ngFirstChars[i] + ngSecondChars[j]);
    }
  }
}

// Test REGEXP_UNICODE to make sure the browser supports Unicode in RegExp.
function testRegexpUnicode() {
  assertTrue('REGEXP_UNICODE', REGEXP_UNICODE);
}

// Test XML10_VERSION_INFO and XML11_VERSION_INFO.
// Also test XML_S and XML_EQ.
function testXmlVersionInfo() {
  var okVersion10 = [
      ' version="1.0"',
      ' version  =   "1.0"',
      '\tversion\t\t=\t\t\t"1.0"',
      '\rversion\r\r=\r\r\r"1.0"',
      '\nversion\n\n=\n\n\n"1.0"',
      '\r\nversion\r\n\r\n=\r\n\r\n\r\n"1.0"',
      '\n\rversion\n\r\n\r=\n\r\n\r\n\r"1.0"',
      ' \t\r\n \t\n\rversion \r\t\n \r\n\t= \n\t\r \n\r\t"1.0"',
      " version='1.0'",
      " version  =   '1.0'",
      "\tversion\t\t=\t\t\t'1.0'",
      "\rversion\r\r=\r\r\r'1.0'",
      "\nversion\n\n=\n\n\n'1.0'",
      "\r\nversion\r\n\r\n=\r\n\r\n\r\n'1.0'",
      "\n\rversion\n\r\n\r=\n\r\n\r\n\r'1.0'",
      "\t \r\n\t \n\rversion\t\r \n\t\r\n =\t\n \r\t\n\r '1.0'"
  ];
  var ngVersion10 = [
      'version="1.0"',    // Must start with space char
      ' Version="1.0"',   // Must be lower letter
      ' VERSION="1.0"',   // Must be lower letter
      ' version"1.0"',    // Must have '='
      ' version "1.0"',   // Must have '='
      ' version\t"1.0"',  // Must have '='
      ' version+"1.0"',   // Must have '='
      ' version-"1.0"',   // Must have '='
      ' version=1.0',     // Must be quoted
      ' version="1.1"',   // Must be '1.0'
      ' version="1"',     // Must be '1.0'
      ' version="10"',    // Must be '1.0'
      ' version="100"',   // Must be '1.0'
      ' version="1-0"',   // Must be '1.0'
      ' version="1_0"'    // Must be '1.0'
  ];
  var okVersion11 = [
      ' version="1.1"',
      ' version  =   "1.1"',
      '\tversion\t\t=\t\t\t"1.1"',
      '\rversion\r\r=\r\r\r"1.1"',
      '\nversion\n\n=\n\n\n"1.1"',
      '\r\nversion\r\n\r\n=\r\n\r\n\r\n"1.1"',
      '\n\rversion\n\r\n\r=\n\r\n\r\n\r"1.1"',
      '\r \t\n\r \n\tversion\r\t \n\r\t\n =\r\n \t\r\n\t "1.1"',
      " version='1.1'",
      " version  =   '1.1'",
      "\tversion\t\t=\t\t\t'1.1'",
      "\rversion\r\r=\r\r\r'1.1'",
      "\nversion\n\n=\n\n\n'1.1'",
      "\r\nversion\r\n\r\n=\r\n\r\n\r\n'1.1'",
      "\n\rversion\n\r\n\r=\n\r\n\r\n\r'1.1'",
      "\n \t\r\n \r\tversion\n\t \r\n\t\r =\n\r \t\n\r\t '1.1'"
  ];
  var ngVersion11 = [
      'version="1.1"',    // Must start with space char
      ' Version="1.1"',   // Must be lower letter
      ' VERSION="1.1"',   // Must be lower letter
      ' version"1.1"',    // Must have '='
      ' version "1.1"',   // Must have '='
      ' version\t"1.1"',  // Must have '='
      ' version+"1.1"',   // Must have '='
      ' version-"1.1"',   // Must have '='
      ' version=1.1',     // Must be quoted
      ' version="1.0"',   // Must be '1.1'
      ' version="1"',     // Must be '1.1'
      ' version="11"',    // Must be '1.1'
      ' version="111"',   // Must be '1.1'
      ' version="1-1"',   // Must be '1.1'
      ' version="1_1"'    // Must be '1.1'
  ];

  var regexp = new RegExp(XML10_VERSION_INFO);
  for (var i = 0; i < okVersion10.length; i++) {
    assertOk('XML10_VERSION_INFO with ok #' + i, regexp,
             okVersion10[i], okVersion10[i]);
  }
  for (var i = 0; i < ngVersion10.length; i++) {
    assertNg('XML10_VERSION_INFO with ng #' + i, regexp,
             ngVersion10[i]);
  }
  var regexp = new RegExp(XML11_VERSION_INFO);
  for (var i = 0; i < okVersion11.length; i++) {
    assertOk('XML11_VERSION_INFO with ok #' + i, regexp,
             okVersion11[i], okVersion11[i]);
  }
  for (var i = 0; i < ngVersion11.length; i++) {
    assertNg('XML11_VERSION_INFO with ng #' + i, regexp,
             ngVersion11[i]);
  }
}

// Test XML_CHAR_REF.
function testXmlCharRef() {
  var okCharRef = [
      '&#0;',
      '&#9;',
      '&#32;',  // ' '
      '&#34;',  // '"'
      '&#38;',  // '&'
      '&#39;',  // "'"
      '&#60;',  // "<"
      '&#62;',  // ">"
      '&#65;',  // "A"
      '&#100;',
      '&#9999;',
      '&#65536;',
      '&#0123456789;',
      '&#9876543210;',
      '&#314159265358979323846264338327950;',  // Pi, until all 0-9 appears
      '&#x0;',
      '&#x9;',
      '&#xA;',
      '&#xF;',
      '&#xa;',
      '&#xf;',
      '&#x20;',  // ' '
      '&#x22;',  // '"'
      '&#x26;',  // '&'
      '&#x27;',  // "'"
      '&#x3c;',  // "<"
      '&#x3e;',  // ">"
      '&#x41;',  // "A"
      '&#xAA;',  // Both upper letter
      '&#xBb;',  // Start with upper letter, end with lower letter
      '&#xcC;',  // Start with lower letter, end with upper letter
      '&#xdd;',  // Both lower letter
      '&#x100;',
      '&#x9999;',
      '&#xaaaa;',
      '&#xffff;',
      '&#xcafebabe;',
      '&#x0123456789ABCDEFabcdef;',
      '&#xfedcbaFEDCBA9876543210;'
  ];
  var ngCharRef = [
      '&0;',    // Must start with '&#'
      '#0;',    // Must start with '&#'
      'x0;',    // Must start with '&#'
      '&#;',    // Must have one or more digit
      '&#/;',   // Must be 0-9 ('/' is a char before '0')
      '&#0',    // Must end with ';'
      '&#9',    // Must end with ';'
      '&#0:',   // Must end with ';'
      '&#9:',   // Must end with ';'
      '&# 0;',  // Must not have a space char
      '&#0 ;',  // Must not have a space char
      '&# 0 ;', // Must not have space chars
      '&#A;',   // Must not have A-Fa-f
      '&#F;',   // Must not have A-Fa-f
      '&#0A;',  // Must not have A-Fa-f
      '&#9F;',  // Must not have A-Fa-f
      '&#0F;',  // Must not have A-Fa-f
      '&#9A;',  // Must not have A-Fa-f
      '&#:;',   // Must be 0-9 (':' is a char after '9')
      '&#x;',   // Must have one or more xdigit
      '&#x/;',  // Must be 0-9A-Fa-f ('/' is a char before '0')
      '&#x0',   // Must end with ';'
      '&#x9',   // Must end with ';'
      '&#xA',   // Must end with ';'
      '&#xF',   // Must end with ';'
      '&#xa',   // Must end with ';'
      '&#xf',   // Must end with ';'
      '&#x0:',  // Must end with ';'
      '&#x9:',  // Must end with ';'
      '&#xA:',  // Must end with ';'
      '&#xF:',  // Must end with ';'
      '&#xa:',  // Must end with ';'
      '&#xf:',  // Must end with ';'
      '&#x 0',  // Must not have a space char
      '&#x0 ',  // Must not have a space char
      '&#x 0 ', // Must not have space chars
      '&#x:;',  // Must be 0-9A-Fa-f (':' is a char after '9')
      '&#x@;',  // Must be 0-9A-Fa-f ('@' is a char before 'A')
      '&#xG;',  // Must be 0-9A-Fa-f
      '&#x`;',  // Must be 0-9A-Fa-f ('`' is a char before 'a')
      '&#xg;'   // Must be 0-9A-Fa-f
  ];

  var regexp = new RegExp(XML_CHAR_REF);
  for (var i = 0; i < okCharRef.length; i++) {
    assertOk('XML_CHAR_REF with ok #' + i, regexp,
             okCharRef[i], okCharRef[i]);
  }
  for (var i = 0; i < ngCharRef.length; i++) {
    assertNg('XML_CHAR_REF with ng #' + i, regexp,
             ngCharRef[i]);
  }
}

// Test XML10_ENTITY_REF and XML11_ENTITY_REF.
// Also test XML10_NAME and XML11_NAME a little bit.
function testXmlEntityRef() {
  var okEntityRef = [
      '&lt;',    // XML predefined entity
      '&gt;',    // XML predefined entity
      '&amp;',   // XML predefined entity
      '&aps;',   // XML predefined entity
      '&quot;',  // XML predefined entity
      '&nbsp;',    // HTML entity (picked up as a real world example)
      '&copy;',    // HTML entity
      '&reg;',     // HTML entity
      '&Agrave;',  // HTML entity, starts with upper letter
      '&ETH;',     // HTML entity, all upper letter
      '&lArr;',    // HTML entity, starts with lower letter, has upper letter
      '&there4;',  // HTML entity, has digit
      '&foo;',               // Just an entity name
      '&:foo;',              // Entity name can start with ':'
      '&_foo;',              // Entity name can start with '_'
      '&foo-bar.baz:quux_;', // Entity name can have '-', '.', ':' or '_'
      '&\u30a8\u30f3\u30c6\u30a3\u30c6\u30a3\u540d;'  // Japanese entity name
  ];
  var ngEntityRef = [
      'lt;',      // Must start with '&'
      '&gt',      // Must end with ';'
      '& amp;',   // Must not have a space char
      '&aps ;',   // Must not have a space char
      '& quot ;', // Must not have space chars
      '&-foo;',   // Entity name must not start with '-'
      '&.foo;'    // Entity name must not start with '.'
  ];
  var edgeEntityRef = [  // Invalid XML10_ENTITY_REF but valid XML11_ENTITY_REF
      '&\u0131\u0132;',
      '&\u0132\u0133;',
      '&\u0133\u0134;',
      '&\u0131\u0132\u0133\u0134;',
      '&\u3001\u3030\u4d00\u9fff\ufffd;',
  ];

  var regexp10 = new RegExp(XML10_ENTITY_REF);
  for (var i = 0; i < okEntityRef.length; i++) {
    assertOk('XML10_ENTITY_REF with ok #' + i, regexp10,
             okEntityRef[i], okEntityRef[i]);
  }
  for (var i = 0; i < ngEntityRef.length; i++) {
    assertNg('XML10_ENTITY_REF with ng #' + i, regexp10,
             ngEntityRef[i]);
  }

  var regexp11 = new RegExp(XML11_ENTITY_REF);
  for (var i = 0; i < okEntityRef.length; i++) {
    assertOk('XML11_ENTITY_REF with ok #' + i, regexp11,
             okEntityRef[i], okEntityRef[i]);
  }
  for (var i = 0; i < ngEntityRef.length; i++) {
    assertNg('XML11_ENTITY_REF with ng #' + i, regexp11,
             ngEntityRef[i]);
  }

  for (var i = 0; i < edgeEntityRef.length; i++) {
    assertNg('XML10_ENTITY_REF with edge #' + i, regexp10,
             edgeEntityRef[i]);
    assertOk('XML11_ENTITY_REF with edge #' + i, regexp11,
             edgeEntityRef[i], edgeEntityRef[i]);
  }
}

// Test XML10_NAME including XML10_LETTER, XML10_NAME_CHAR, XML10_BASE_CHAR,
// XML10_IDEOGRAPHIC, XML10_DIGIT, XML10_COMBINING_CHAR and XML10_EXTENDER.
function testXml10Name() {
  var okFirstChars = [
      '\u003a',  // ':'
      '\u0041',  // 'A' : XML10_BASE_CHAR
      '\u005a',  // 'Z' : XML10_BASE_CHAR
      '\u005f',  // '_'
      '\u0061',  // 'a' : XML10_BASE_CHAR
      '\u007a',  // 'z' : XML10_BASE_CHAR
      '\u00c0',  // XML10_BASE_CHAR
      '\u00d6',  // XML10_BASE_CHAR
      '\u00d8',  // XML10_BASE_CHAR
      '\u00f6',  // XML10_BASE_CHAR
      '\u00f8',  // XML10_BASE_CHAR
      '\u00ff',  // XML10_BASE_CHAR
      '\u0100',  // XML10_BASE_CHAR
      '\u4e00',  // XML10_IDEOGRAPHIC
      '\u9fa5',  // XML10_IDEOGRAPHIC
      '\uac00',  // XML10_BASE_CHAR
      '\ud7a3'   // XML10_BASE_CHAR
  ];
  var ngFirstChars = [
      '\u002d',  // '-'
      '\u002e',  // '.'
      '\u0030',  // '0' : First char of XML10_DIGIT
      '\u0039',  // '9' : XML10_DIGIT
      '\u003b',  // ';' : Non XML10_BASE_CHAR
      '\u0040',  // '@' : Non XML10_BASE_CHAR
      '\u005b',  // '[' : Non XML10_BASE_CHAR
      '\u005e',  // '^' : Non XML10_BASE_CHAR
      '\u0060',  // '`' : Non XML10_BASE_CHAR
      '\u007b',  // '{' : Non XML10_BASE_CHAR
      '\u00b7',  // First char of XML10_EXTENDER
      '\u00bf',  // Non XML10_BASE_CHAR
      '\u00d7',  // Non XML10_BASE_CHAR
      '\u00f7',  // Non XML10_BASE_CHAR
      '\u0300',  // First char of XML10_COMBINING_CHAR
      '\u0660',  // XML10_DIGIT
      '\u0f29',  // Last char of XML10_DIGIT
      '\u309a',  // Last char of XML10_COMBINING_CHAR
      '\u30fe',  // Last char of XML10_EXTENDER
      '\u4dff',  // Edge char for XML10_IDEOGRAPHIC
      '\u9fa6',  // Edge char for XML10_IDEOGRAPHIC
      '\uabff',  // Edge char for XML10_BASE_CHAR
      '\ud7a4'   // Edge char for XML10_BASE_CHAR
  ];
  var okSecondChars = [
      '\u002d',  // '-'
      '\u002e',  // '.'
      '\u0030',  // '0' : XML10_DIGIT
      '\u0039',  // '9' : XML10_DIGIT
      '\u003a',  // ':'
      '\u0041',  // 'A' : XML10_BASE_CHAR
      '\u005a',  // 'Z' : XML10_BASE_CHAR
      '\u005f',  // '_'
      '\u0061',  // 'a' : XML10_BASE_CHAR
      '\u007a',  // 'z' : XML10_BASE_CHAR
      '\u00b7',  // XML10_EXTENDER
      '\u00c0',  // XML10_BASE_CHAR
      '\u00d6',  // XML10_BASE_CHAR
      '\u00d8',  // XML10_BASE_CHAR
      '\u00f6',  // XML10_BASE_CHAR
      '\u00f8',  // XML10_BASE_CHAR
      '\u00ff',  // XML10_BASE_CHAR
      '\u0100',  // XML10_BASE_CHAR
      '\u0300',  // XML10_COMBINING_CHAR
      '\u0660',  // XML10_DIGIT
      '\u0f29',  // XML10_DIGIT
      '\u309a',  // XML10_COMBINING_CHAR
      '\u30fe',  // XML10_EXTENDER
      '\u4e00',  // XML10_IDEOGRAPHIC
      '\u9fa5',  // XML10_IDEOGRAPHIC
      '\uac00',  // XML10_BASE_CHAR
      '\ud7a3'   // XML10_BASE_CHAR
  ];
  var ngSecondChars = [
      '\u0040',  // '@' : Non XML10_BASE_CHAR
      '\u005b',  // '[' : Non XML10_BASE_CHAR
      '\u0060',  // '`' : Non XML10_BASE_CHAR
      '\u007b',  // '{' : Non XML10_BASE_CHAR
      '\u00b6',  // Edge char for XML10_EXTENDER
      '\u00bf',  // Non XML10_BASE_CHAR
      '\u00d7',  // Non XML10_BASE_CHAR
      '\u00f7',  // Non XML10_BASE_CHAR
      '\u02ff',  // Edge char for XML10_COMBINING_CHAR
      '\u309b',  // Edge char for XML10_COMBINING_CHAR
      '\u30ff',  // Edge char for XML10_EXTENDER
      '\u4dff',  // Edge char for XML10_IDEOGRAPHIC
      '\u9fa6',  // Edge char for XML10_IDEOGRAPHIC
      '\uabff',  // Edge char for XML10_BASE_CHAR
      '\ud7a4'   // Edge char for XML10_BASE_CHAR
  ];

  doTestXmlName('XML10_NAME', new RegExp(XML10_NAME),
                okFirstChars, ngFirstChars, okSecondChars, ngSecondChars);
}

// Test strings for testXml10Attribute() and testXml11Attribute().
var okAttValues = [
    '""',
    '"foo"',
    '"&lt;foobar"',
    '"foo&lt;bar"',
    '"foobar&lt;"',
    '"&gt;foobar"',
    '"foo&gt;bar"',
    '"foobar&gt;"',
    '"&amp;foobar"',
    '"foo&amp;bar"',
    '"foobar&amp;"',
    '"&quot;foobar"',
    '"foo&quot;bar"',
    '"foobar&quot;"',
    '"\'foobar"',
    '"foo\'bar"',
    '"foobar\'"',
    '"&lt;&amp;&quot;&apos;\'"',
    '"&#102;&#111;&#111;"',  // "foo"
    '"&#x66;&#x6f;&#x6f;"',  // "foo"
    '"&lt;f&#111;&#x6f;>"',  // "<foo>"
    '"&:foo;"',
    '"&_foo;"',
    '"&foo-bar.baz:quux_;"',
    '"foo&\u30a8\u30f3\u30c6\u30a3\u30c6\u30a3\u540d;bar"',
    "''",
    "'foo'",
    "'&lt;foobar'",
    "'foo&lt;bar'",
    "'foobar&lt;'",
    "'&gt;foobar'",
    "'foo&gt;bar'",
    "'foobar&gt;'",
    "'&amp;foobar'",
    "'foo&amp;bar'",
    "'foobar&amp;'",
    "'&apos;foobar'",
    "'foo&apos;bar'",
    "'foobar&apos;'",
    "'\"foobar'",
    "'foo\"bar'",
    "'foobar\"'",
    "'&lt;&gt;&amp;&quot;&apos;\"'",
    "'&#102;&#111;&#111;'",  // 'foo'
    "'&#x66;&#x6f;&#x6f;'",  // 'foo'
    "'&lt;f&#111;&#x6f;>'",   // '<foo>'
    "'&:foo;'",
    "'&_foo;'",
    "'&foo-bar.baz:quux_;'",
    "'foo&\u30a8\u30f3\u30c6\u30a3\u30c6\u30a3\u540d;bar'"
];
var ngAttValues = [
    '',
    'foo',        // Must start with '"', end with '"'
    'foo"',       // Must start with '"'
    '"foo',       // Must end with '"'
    '"foo\'',     // Must end with '"'
    '"<foobar"',  // Must not have '<'
    '"foo<bar"',  // Must not have '<'
    '"foobar<"',  // Must not have '<'
    '"&foobar"',  // Must not have '&' that is not an entity ref or char ref
    '"foo&bar"',  // Must not have '&' that is not an entity ref or char ref
    '"foobar&"',  // Must not have '&' that is not an entity ref or char ref
    '"&-foo;"',   // Entity name must be a valid XML10_NAME
    '"&.foo;"',   // Entity name must be a valid XML10_NAME
    "",
    "foo",        // Must start with "'", end with "'"
    "foo'",       // Must start with "'"
    "'foo",       // Must end with "'"
    "'foo\"",     // Must end with "'"
    "'<foobar'",  // Must not have "<"
    "'foo<bar'",  // Must not have "<"
    "'foobar<'",  // Must not have "<"
    "'&foobar'",  // Must not have "&" that is not an entity ref or char ref
    "'foo&bar'",  // Must not have "&" that is not an entity ref or char ref
    "'foobar&'",  // Must not have "&" that is not an entity ref or char ref
    "'&-foo;'",   // Entity name must be a valid XML10_NAME
    "'&.foo;'"    // Entity name must be a valid XML10_NAME
];
var ngAttValues2 = [
    '""foobar"', '""',
    '"foo"bar"', '"foo"',
    '"foobar""', '"foobar"',
    "''foobar'", "''",
    "'foo'bar'", "'foo'",
    "'foobar''", "'foobar'"
];
var edgeAttValues = [  // Invalid XML10_ATT_VALUE but valid XML11_ATT_VALUE
    '"&\u0131\u0132;foo"',
    '"f&\u0132\u0133;oo"',
    '"fo&\u0133\u0134;o"',
    '"foo&\u0131\u0132\u0133\u0134;bar"',
    '"baz&\u3001\u3030\u4d00\u9fff;quux"',
    "'&\u0131\u0132;foo'",
    "'f&\u0132\u0133;oo'",
    "'fo&\u0133\u0134;o'",
    "'foo&\u0131\u0132\u0133\u0134;bar'",
    "'baz&\u3001\u3030\u4d00\u9fff;quux'"
];

// Test XML10_ATTRIBUTE including XML10_ATT_VALUE and XML10_REFERENCE.
// A difference from testXml11Attribute() is that tests with edge cases
// should fail here.
function testXml10Attribute() {
  var regexp = new RegExp(XML10_ATT_VALUE);
  for (var i = 0; i < okAttValues.length; i++) {
    assertOk('XML10_ATT_VALUE with ok #' + i, regexp,
             okAttValues[i], okAttValues[i]);
  }
  for (var i = 0; i < ngAttValues.length; i++) {
    assertNg('XML10_ATT_VALUE with ng #' + i, regexp,
             ngAttValues[i]);
  }
  for (var i = 0; i < ngAttValues2.length; i += 2) {
    assertOk('XML10_ATT_VALUE with ng2 #' + (i / 2), regexp,
             ngAttValues2[i], ngAttValues2[i + 1]);
  }
  for (var i = 0; i < edgeAttValues.length; i++) {
    assertNg('XML10_ATT_VALUE with ng2 #' + i, regexp,
             edgeAttValues[i]);
  }
}

// Test XML11_NAME, including XML11_NAME_START_CHAR and XML11_NAME_CHAR.
function testXml11Name() {
  var okFirstChars = [
      '\u003a',  // ':'
      '\u0041',  // 'A' : XML11_NAME_START_CHAR
      '\u005a',  // 'Z' : XML11_NAME_START_CHAR
      '\u005f',  // '_'
      '\u0061',  // 'a' : XML11_NAME_START_CHAR
      '\u007a',  // 'z' : XML11_NAME_START_CHAR
      '\u00c0',  // XML11_NAME_START_CHAR
      '\u00d6',  // XML11_NAME_START_CHAR
      '\u00d8',  // XML11_NAME_START_CHAR
      '\u00f6',  // XML11_NAME_START_CHAR
      '\u00f8',  // XML11_NAME_START_CHAR
      '\u02ff',  // XML11_NAME_START_CHAR
      '\u0370',  // XML11_NAME_START_CHAR
      '\u037d',  // XML11_NAME_START_CHAR
      '\u037f',  // XML11_NAME_START_CHAR
      '\u1fff',  // XML11_NAME_START_CHAR
      '\u200c',  // XML11_NAME_START_CHAR
      '\u200d',  // XML11_NAME_START_CHAR
      '\u2070',  // XML11_NAME_START_CHAR
      '\u218f',  // XML11_NAME_START_CHAR
      '\u2c00',  // XML11_NAME_START_CHAR
      '\u2fef',  // XML11_NAME_START_CHAR
      '\u3001',  // XML11_NAME_START_CHAR
      '\ud7ff',  // XML11_NAME_START_CHAR
      '\uf900',  // XML11_NAME_START_CHAR
      '\ufdcf',  // XML11_NAME_START_CHAR
      '\ufdf0',  // XML11_NAME_START_CHAR
      '\ufffd'   // XML11_NAME_START_CHAR
  ];
  var ngFirstChars = [
      '\u002d',  // '-' : XML11_NAME_CHAR
      '\u002e',  // '.' : XML11_NAME_CHAR
      '\u0030',  // '0' : XML11_NAME_CHAR
      '\u0039',  // '9' : XML11_NAME_CHAR
      '\u003b',  // ';' : Non XML11_NAME_START_CHAR
      '\u0040',  // '@' : Non XML11_NAME_START_CHAR
      '\u005b',  // '[' : Non XML11_NAME_START_CHAR
      '\u005e',  // '^' : Non XML11_NAME_START_CHAR
      '\u0060',  // '`' : Non XML11_NAME_START_CHAR
      '\u007b',  // '{' : Non XML11_NAME_START_CHAR
      '\u00b7',  // XML11_NAME_CHAR
      '\u00bf',  // Non XML11_NAME_START_CHAR
      '\u00d7',  // Non XML11_NAME_START_CHAR
      '\u00f7',  // Non XML11_NAME_START_CHAR
      '\u0300',  // Non XML11_NAME_START_CHAR / XML11_NAME_CHAR
      '\u036f',  // Non XML11_NAME_START_CHAR / XML11_NAME_CHAR
      '\u037e',  // Non XML11_NAME_START_CHAR
      '\u2000',  // Non XML11_NAME_START_CHAR
      '\u200b',  // Non XML11_NAME_START_CHAR
      '\u200e',  // Non XML11_NAME_START_CHAR
      '\u203f',  // XML11_NAME_CHAR
      '\u2040',  // XML11_NAME_CHAR
      '\u206f',  // Non XML11_NAME_START_CHAR
      '\u2190',  // Non XML11_NAME_START_CHAR
      '\u2bff',  // Non XML11_NAME_START_CHAR
      '\u2ff0',  // Non XML11_NAME_START_CHAR
      '\u3000',  // Non XML11_NAME_START_CHAR
      '\ud800',  // Non XML11_NAME_START_CHAR
      '\uf8ff',  // Non XML11_NAME_START_CHAR
      '\ufdd0',  // Non XML11_NAME_START_CHAR
      '\ufdef',  // Non XML11_NAME_START_CHAR
      '\uffff'   // Non XML11_NAME_START_CHAR
  ];
  var okSecondChars = [
      '\u002d',  // '-' : XML11_NAME_CHAR
      '\u002e',  // '.' : XML11_NAME_CHAR
      '\u0030',  // '0' : XML11_NAME_CHAR
      '\u0039',  // '9' : XML11_NAME_CHAR
      '\u003a',  // ':'
      '\u0041',  // 'A' : XML11_NAME_START_CHAR
      '\u005a',  // 'Z' : XML11_NAME_START_CHAR
      '\u005f',  // '_'
      '\u0061',  // 'a' : XML11_NAME_START_CHAR
      '\u007a',  // 'z' : XML11_NAME_START_CHAR
      '\u00b7',  // XML11_NAME_CHAR
      '\u00c0',  // XML11_NAME_START_CHAR
      '\u00d6',  // XML11_NAME_START_CHAR
      '\u00d8',  // XML11_NAME_START_CHAR
      '\u00f6',  // XML11_NAME_START_CHAR
      '\u00f8',  // XML11_NAME_START_CHAR
      '\u02ff',  // XML11_NAME_START_CHAR
      '\u0300',  // XML11_NAME_CHAR
      '\u036f',  // XML11_NAME_CHAR
      '\u0370',  // XML11_NAME_START_CHAR
      '\u037d',  // XML11_NAME_START_CHAR
      '\u037f',  // XML11_NAME_START_CHAR
      '\u1fff',  // XML11_NAME_START_CHAR
      '\u200c',  // XML11_NAME_START_CHAR
      '\u200d',  // XML11_NAME_START_CHAR
      '\u203f',  // XML11_NAME_CHAR
      '\u2040',  // XML11_NAME_CHAR
      '\u2070',  // XML11_NAME_START_CHAR
      '\u218f',  // XML11_NAME_START_CHAR
      '\u2c00',  // XML11_NAME_START_CHAR
      '\u2fef',  // XML11_NAME_START_CHAR
      '\u3001',  // XML11_NAME_START_CHAR
      '\ud7ff',  // XML11_NAME_START_CHAR
      '\uf900',  // XML11_NAME_START_CHAR
      '\ufdcf',  // XML11_NAME_START_CHAR
      '\ufdf0',  // XML11_NAME_START_CHAR
      '\ufffd'   // XML11_NAME_START_CHAR
  ];
  var ngSecondChars = [
      '\u003b',  // ';' : Non XML11_NAME_START_CHAR
      '\u0040',  // '@' : Non XML11_NAME_START_CHAR
      '\u005b',  // '[' : Non XML11_NAME_START_CHAR
      '\u005e',  // '^' : Non XML11_NAME_START_CHAR
      '\u0060',  // '`' : Non XML11_NAME_START_CHAR
      '\u007b',  // '{' : Non XML11_NAME_START_CHAR
      '\u00bf',  // Non XML11_NAME_START_CHAR
      '\u00d7',  // Non XML11_NAME_START_CHAR
      '\u00f7',  // Non XML11_NAME_START_CHAR
      '\u037e',  // Non XML11_NAME_START_CHAR
      '\u2000',  // Non XML11_NAME_START_CHAR
      '\u200b',  // Non XML11_NAME_START_CHAR
      '\u200e',  // Non XML11_NAME_START_CHAR
      '\u206f',  // Non XML11_NAME_START_CHAR
      '\u2190',  // Non XML11_NAME_START_CHAR
      '\u2bff',  // Non XML11_NAME_START_CHAR
      '\u2ff0',  // Non XML11_NAME_START_CHAR
      '\u3000',  // Non XML11_NAME_START_CHAR
      '\ud800',  // Non XML11_NAME_START_CHAR
      '\uf8ff',  // Non XML11_NAME_START_CHAR
      '\ufdd0',  // Non XML11_NAME_START_CHAR
      '\ufdef',  // Non XML11_NAME_START_CHAR
      '\uffff'   // Non XML11_NAME_START_CHAR
  ];

  doTestXmlName('XML11_NAME', new RegExp(XML11_NAME),
                okFirstChars, ngFirstChars, okSecondChars, ngSecondChars);
}

// Test XML11_ATTRIBUTE including XML11_ATT_VALUE and XML11_REFERENCE.
// A difference from testXml10Attribute() is that tests with edge cases
// should succeed here.
function testXml11Attribute() {
  var regexp = new RegExp(XML11_ATT_VALUE);
  for (var i = 0; i < okAttValues.length; i++) {
    assertOk('XML11_ATT_VALUE with ok #' + i, regexp,
             okAttValues[i], okAttValues[i]);
  }
  for (var i = 0; i < ngAttValues.length; i++) {
    assertNg('XML11_ATT_VALUE with ng #' + i, regexp,
             ngAttValues[i]);
  }
  for (var i = 0; i < ngAttValues2.length; i += 2) {
    assertOk('XML11_ATT_VALUE with ng2 #' + (i / 2), regexp,
             ngAttValues2[i], ngAttValues2[i + 1]);
  }
  for (var i = 0; i < edgeAttValues.length; i++) {
    assertOk('XML11_ATT_VALUE with ng2 #' + i, regexp,
             edgeAttValues[i], edgeAttValues[i]);
  }
}

// Test XML_NC_NAME including XML_NC_NAME_CHAR.
// One difference from testXml10Name() is that ':' is invalid here.
function testXmlNcName() {
  var okFirstChars = [
      '\u0041',  // 'A' : XML10_BASE_CHAR
      '\u005a',  // 'Z' : XML10_BASE_CHAR
      '\u005f',  // '_'
      '\u0061',  // 'a' : XML10_BASE_CHAR
      '\u007a',  // 'z' : XML10_BASE_CHAR
      '\u00c0',  // XML10_BASE_CHAR
      '\u00d6',  // XML10_BASE_CHAR
      '\u00d8',  // XML10_BASE_CHAR
      '\u00f6',  // XML10_BASE_CHAR
      '\u00f8',  // XML10_BASE_CHAR
      '\u00ff',  // XML10_BASE_CHAR
      '\u0100',  // XML10_BASE_CHAR
      '\u4e00',  // XML10_IDEOGRAPHIC
      '\u9fa5',  // XML10_IDEOGRAPHIC
      '\uac00',  // XML10_BASE_CHAR
      '\ud7a3'   // XML10_BASE_CHAR
  ];
  var ngFirstChars = [
      '\u002d',  // '-'
      '\u002e',  // '.'
      '\u0030',  // '0' : First char of XML10_DIGIT
      '\u0039',  // '9' : XML10_DIGIT
      '\u003a',  // ':' : Valid in XML10_NAME but invalid in XML_NC_NAME
      '\u0040',  // '@' : Non XML10_BASE_CHAR
      '\u005b',  // '[' : Non XML10_BASE_CHAR
      '\u0060',  // '`' : Non XML10_BASE_CHAR
      '\u007b',  // '{' : Non XML10_BASE_CHAR
      '\u00b7',  // First char of XML10_EXTENDER
      '\u00bf',  // Non XML10_BASE_CHAR
      '\u00d7',  // Non XML10_BASE_CHAR
      '\u00f7',  // Non XML10_BASE_CHAR
      '\u0660',  // XML10_DIGIT
      '\u0300',  // First char of XML10_COMBINING_CHAR
      '\u0f29',  // Last char of XML10_DIGIT
      '\u309a',  // Last char of XML10_COMBINING_CHAR
      '\u30fe',  // Last char of XML10_EXTENDER
      '\u4dff',  // Edge char for XML10_IDEOGRAPHIC
      '\u9fa6',  // Edge char for XML10_IDEOGRAPHIC
      '\uabff',  // Edge char for XML10_BASE_CHAR
      '\ud7a4'   // Edge char for XML10_BASE_CHAR
  ];
  var okSecondChars = [
      '\u002d',  // '-'
      '\u002e',  // '.'
      '\u0030',  // '0' : XML10_DIGIT
      '\u0039',  // '9' : XML10_DIGIT
      '\u0041',  // 'A' : XML10_BASE_CHAR
      '\u005a',  // 'Z' : XML10_BASE_CHAR
      '\u005f',  // '_'
      '\u0061',  // 'a' : XML10_BASE_CHAR
      '\u007a',  // 'z' : XML10_BASE_CHAR
      '\u00b7',  // XML10_EXTENDER
      '\u00c0',  // XML10_BASE_CHAR
      '\u00d6',  // XML10_BASE_CHAR
      '\u00d8',  // XML10_BASE_CHAR
      '\u00f6',  // XML10_BASE_CHAR
      '\u00f8',  // XML10_BASE_CHAR
      '\u00ff',  // XML10_BASE_CHAR
      '\u0100',  // XML10_BASE_CHAR
      '\u0300',  // XML10_COMBINING_CHAR
      '\u309a',  // XML10_COMBINING_CHAR
      '\u30fe',  // XML10_EXTENDER
      '\u0660',  // XML10_DIGIT
      '\u0f29',  // XML10_DIGIT
      '\u4e00',  // XML10_IDEOGRAPHIC
      '\u9fa5',  // XML10_IDEOGRAPHIC
      '\uac00',  // XML10_BASE_CHAR
      '\ud7a3'   // XML10_BASE_CHAR
  ];
  var ngSecondChars = [
      '\u003a',  // ':' : Valid in XML10_NAME but invalid in XML_NC_NAME
      '\u0040',  // '@' : Non XML10_BASE_CHAR
      '\u005b',  // '[' : Non XML10_BASE_CHAR
      '\u0060',  // '`' : Non XML10_BASE_CHAR
      '\u007b',  // '{' : Non XML10_BASE_CHAR
      '\u00b6',  // Edge char for XML10_EXTENDER
      '\u00bf',  // Non XML10_BASE_CHAR
      '\u00d7',  // Non XML10_BASE_CHAR
      '\u00f7',  // Non XML10_BASE_CHAR
      '\u02ff',  // Edge char for XML10_COMBINING_CHAR
      '\u309b',  // Edge char for XML10_COMBINING_CHAR
      '\u30ff',  // Edge char for XML10_EXTENDER
      '\u4dff',  // Edge char for XML10_IDEOGRAPHIC
      '\u9fa6',  // Edge char for XML10_IDEOGRAPHIC
      '\uabff',  // Edge char for XML10_BASE_CHAR
      '\ud7a4'   // Edge char for XML10_BASE_CHAR
  ];

  doTestXmlName('XML_NC_NAME', new RegExp(XML_NC_NAME),
                okFirstChars, ngFirstChars, okSecondChars, ngSecondChars);
}
