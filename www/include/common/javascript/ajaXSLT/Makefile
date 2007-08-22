# Currently, this Makefile has only a "dist" target.

PACKAGE = ajaxslt
VERSION = 0.5

DIST_FILES = \
  AUTHORS \
  ChangeLog \
  COPYING \
  Makefile \
  README \
  TODO \
  dom.js \
  dom_unittest.html \
  dom_unittest.js \
  simplelog.js \
  test/xpath.html \
  test/xpath_script.js \
  test/xslt.html \
  test/xslt_script.js \
  util.js \
  xmltoken.js \
  xmltoken_unittest.html \
  xmltoken_unittest.js \
  xpathdebug.js \
  xpath.js \
  xpath_unittest.html \
  xpath_unittest.js \
  xslt.js \
  xslt_unittest.html \
  xslt_unittest.js \

DIST_NAME = $(PACKAGE)-$(VERSION)
DIST_DIR = /tmp/$(DIST_NAME)

dist: clobber $(DIST_FILES)
	rm -rf $(DIST_DIR)
	mkdir $(DIST_DIR)
	cp -pr $(DIST_FILES) $(DIST_DIR)
	chmod -R a+r $(DIST_DIR)
	chmod -R u+w $(DIST_DIR)
	chmod -R go-w $(DIST_DIR)
	cd $(DIST_DIR)/.. ; tar cvf $(DIST_NAME).tar $(DIST_NAME) ; gzip $(DIST_NAME).tar
	mv $(DIST_DIR).tar.gz .
	rm -rf $(DIST_DIR)


clobber:
