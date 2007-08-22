// Copyright 2005 Google Inc.
// All Rights Reserved
//
// Unit test for dom.js. This also uses DOMParser, so it only runs
// in firefox.
//
// Author: Steffen Meschkat <mesch@google.com>
//         Junji Takagi <jtakagi@google.com>

function testXmlParse() {

  var xml = [
      '<page>',
      '<request>',
      '<q id="q">new york</q>',
      '</request>',
      '<location lat="100" ', "lon='200'/>",
      '</page>'
  ].join('');

  dom = xmlParse(xml);
  dom1 = (new DOMParser).parseFromString(xml, 'text/xml');
  doTestXmlParse(dom, dom1);

  dom = xmlParse('<?xml version="1.0"?>' + xml);
  dom1 = xmlParse("<?xml version='1.1'?>" + xml);
  doTestXmlParse(dom, dom1);

  var tag = 'q';
  var byTag = dom.getElementsByTagName(tag);
  assertEquals(1, byTag.length);
  assertEquals(tag, byTag[0].nodeName);

  var id = 'q';
  var byId = dom.getElementById(id);
  assertNotNull(byId);
  assertEquals(id, byId.getAttribute('id'));
}

function testXmlParseWeird() {

  var xml = [
      '<_>',
      '<_.:->',
      '<:>!"#$%&\'()*+,-./:;&lt;=&gt;?[\\]^_`{|}~</:>',
      '</_.:->',
      '<:-_. _=".-" :="-."/>',
      '</_>'
  ].join('');

  // DOMParser seems not supporting a tagname that starts with ':', so
  // avoid comparing xmlParse() and DomParser.parseFromString() here.

  dom = xmlParse('<?xml version="1.0"?>' + xml);
  dom1 = xmlParse("<?xml version='1.1'?>" + xml);
  doTestXmlParse(dom, dom1);
}

function testXmlParseJapanese() {

  var xml = [
      '<\u30da\u30fc\u30b8>',
      '<\u30ea\u30af\u30a8\u30b9\u30c8>',
      '<\u30af\u30a8\u30ea>\u6771\u4eac</\u30af\u30a8\u30ea>',
      '</\u30ea\u30af\u30a8\u30b9\u30c8>',
      '<\u4f4d\u7f6e \u7def\u5ea6="\u4e09\u5341\u4e94" ',
      "\u7d4c\u5ea6='\u767e\u56db\u5341'/>",
      '</\u30da\u30fc\u30b8>'
  ].join('');

  dom = xmlParse(xml);
  dom1 = (new DOMParser).parseFromString(xml, 'text/xml');
  doTestXmlParse(dom, dom1);

  dom = xmlParse('<?xml version="1.0"?>' + xml);
  dom1 = xmlParse("<?xml version='1.1'?>" + xml);
  doTestXmlParse(dom, dom1);
}

function doTestXmlParse(dom, dom1) {
  assertEquals('xmlText', xmlText(dom), xmlText(dom1));

  assertEquals('#document',
               dom.nodeName,
               dom1.nodeName);

  assertEquals('documentElement', dom.documentElement, dom.firstChild);
  assertEquals('documentElement', dom1.documentElement, dom1.firstChild);

  assertEquals('parentNode', dom.parentNode, null);
  assertEquals('parentNode', dom1.parentNode, null);

  assertEquals('parentNode', dom.documentElement.parentNode, dom);
  assertEquals('parentNode', dom1.documentElement.parentNode, dom1);

  assertEquals('page',
               dom.documentElement.nodeName,
               dom1.documentElement.nodeName);
  assertEquals('dom.childNodes.length',
               dom.childNodes.length,
               dom1.childNodes.length);
  assertEquals('dom.childNodes.length',
               dom.childNodes.length,
               dom1.childNodes.length);
  assertEquals('page.childNodes.length',
               dom.firstChild.childNodes.length,
               dom1.firstChild.childNodes.length);
  assertEquals('page.childNodes.length',
               dom.firstChild.childNodes.length,
               dom1.firstChild.childNodes.length);

  assertEquals('location.attributes.length',
               dom.firstChild.childNodes[1].attributes.length,
               dom1.firstChild.childNodes[1].attributes.length);
  assertEquals('location.attributes.length',
               dom.firstChild.childNodes[1].attributes.length, 2);
}


function testXmlResolveEntities() {
  assertEquals('";"', xmlResolveEntities('&quot;;&quot;'));
}
