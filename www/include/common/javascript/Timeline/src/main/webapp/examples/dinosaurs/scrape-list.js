// list
var prefixRDF = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
var prefixDC = 'http://purl.org/dc/elements/1.1/';

var namespace = doc.documentElement.namespaceURI;
var nsResolver = namespace ? function(prefix) {
  if (prefix == 'x') return namespace; else return null;
} : null;

var getNode = function(doc, contextNode, xpath, nsResolver) {
  return doc.evaluate(xpath, contextNode, nsResolver, XPathResult.ANY_TYPE,null).iterateNext();
}

var cleanString = function(s) {
  return utilities.trimString(s);
}

var xpath = '//div[@id="dino-container"]/table[@class="dataTable_ms"]/tbody/tr/td/p/i/a/../../../..';
var elmts = utilities.gatherElementsOnXPath(doc, doc, xpath, nsResolver);
for (var i = 0; i < elmts.length; i++) {
  var elmt = elmts[i];
  // elmt.style.backgroundColor = 'red';
  
  var uri = 'item' + i; // generate the item's URI here
  
  model.addStatement(uri, prefixRDF + 'type', 'unknown', false); // Use your own type here
  // utilities.debugPrint('Scraping URI ' + uri);
  
  try {
    var name = cleanString(getNode(doc, elmt, './TD[1]/P[1]/I[1]/A[1]/text()[1]', nsResolver).nodeValue);
  } catch (e) { utilities.debugPrint(e);}
  
  try {
    var taxonomy = cleanString(getNode(doc, elmt, './TD[3]/P[1]/text()[1]', nsResolver).nodeValue);
    taxonomy = taxonomy.replace(/\s+/g, '');
  } catch (e) { utilities.debugPrint(e);}
  
  try {
    var bodyShape = cleanString(getNode(doc, elmt, './TD[5]/IMG[1]', nsResolver).src);
  } catch (e) { bodyShape = ""; }
  utilities.debugPrint(name + "\t" + bodyShape + "\t" + taxonomy);
}
