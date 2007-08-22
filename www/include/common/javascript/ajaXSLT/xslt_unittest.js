// Copyright 2006, Google Inc.
// All Rights Reserved.
//
// Unit test for the XSLT processor.
//
// Author: Steffen Meschkat <mesch@google.com>


function el(id) {
  return document.getElementById(id);
}

function testForEachSort() {
  var xml = xmlParse(el('xml').value);
  var xslt = xmlParse(el('xslt-for-each-sort').value);
  var html = xsltProcess(xml, xslt);
  assertEquals("CAB", html);
}

function testForEachSortAscending() {
  var xml = xmlParse(el('xml').value);
  var xslt = xmlParse(el('xslt-for-each-sort-ascending').value);
  var html = xsltProcess(xml, xslt);
  assertEquals("ABC", html);
}

function testForEachSortDescending() {
  var xml = xmlParse(el('xml').value);
  var xslt = xmlParse(el('xslt-for-each-sort-descending').value);
  var html = xsltProcess(xml, xslt);
  assertEquals("CBA", html);
}

function testApplyTemplates() {
  var xml = xmlParse(el('xml-apply-templates').value);
  var xslt = xmlParse(el('xslt-apply-templates').value);
  var html = xsltProcess(xml, xslt);
  assertEquals("ABC", html);
}

function testGlobalVariables() {
  var xml = xmlParse(el('xml').value);
  var xslt = xmlParse(el('xslt-global-variables').value);
  var html = xsltProcess(xml, xslt);
  assertEquals("xzyyy", html);
}

function testTopLevelOutput() {
  var xml = xmlParse(el('xml').value);
  var xslt = xmlParse(el('xslt-top-level-output').value);
  var html = xsltProcess(xml, xslt);
  assertEquals('<x y="z">k</x>', html);
}

function testCopy() {
  var xml = xmlParse(el('xml').value);
  var xslt = xmlParse(el('xslt-copy').value);
  var html = xsltProcess(xml, xslt);
  assertEquals('<item pos="2">A</item>' +
               '<item pos="3">B</item>' +
               '<item pos="1">C</item>', html);
}
