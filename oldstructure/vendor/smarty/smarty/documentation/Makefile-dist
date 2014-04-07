STYLESHEETS_DIR = /usr/share/xml/docbook/stylesheet/docbook-xsl
PHP = /usr/bin/php
ZIP = /usr/bin/zip
FOP = /usr/bin/fop
XMLTO = /usr/bin/xmlto
XSLTPROC = /usr/bin/xsltproc
WINE = /usr/bin/wine
LANG = en
LANGS_AVAIL = de en es fr it ja pt_BR ru

all: html pdf

allweb: html htmlweb pdf

html: set-current-lang-symlink set-date
	rm -rf manual-$(LANG)
	$(XMLTO) html \
           -o manual-$(LANG) \
           --stringparam use.id.as.filename=1 \
           --stringparam chunker.output.encoding='UTF-8' \
           --stringparam chunker.output.indent='yes' \
           -x $(STYLESHEETS_DIR)/html/chunk.xsl \
           manual.xml
	$(ZIP) -r manual-$(LANG).zip manual-$(LANG)

set-current-lang-symlink:
	rm -f current_lang
	ln -s $(LANG) current_lang

set-date:
	sed "s/{DATE}/`date '+%Y-%m-%d'`/" entities/version.ent.in > entities/version.ent

fo:
	$(XSLTPROC) -o manual-$(LANG).fo $(STYLESHEETS_DIR)/fo/docbook.xsl manual.xml

pdf: set-current-lang-symlink set-date fo
	$(FOP) \
            -c fop/$(LANG).conf \
            -pdf manual-$(LANG).pdf \
            -fo manual-$(LANG).fo
	rm -f manual-$(LANG).fo

htmlweb: set-current-lang-symlink set-date
	rm -rf manualweb-$(LANG) htmlweb-$(LANG)
	$(XMLTO) html \
           -o manualweb-$(LANG) \
           --stringparam use.id.as.filename=1 \
           --stringparam html.ext='.tpl' \
           --stringparam chunker.output.encoding='UTF-8' \
           --stringparam chunker.output.indent='yes' \
           -x $(STYLESHEETS_DIR)/html/chunk.xsl \
           manual.xml
	$(PHP) -f scripts/makeweb.php manualweb-$(LANG) htmlweb-$(LANG)
	rm -rf manualweb-$(LANG)

chm: set-current-lang-symlink set-date
	rm -rf htmlhelp-$(LANG)
	$(XSLTPROC) \
	 -o htmlhelp-$(LANG)/ \
	 --stringparam htmlhelp.encoding 'UTF-8' \
	 --stringparam htmlhelp.enhanced.decompilation 1  \
	 --stringparam htmlhelp.hhc.binary 1 \
	 --stringparam htmlhelp.autolabel 1 \
     --stringparam suppress.navigation 0 \
	 --stringparam htmlhelp.default.topic 'make_chm_index.html' \
	 --stringparam htmlhelp.use.hhk 1 \
	 $(STYLESHEETS_DIR)/htmlhelp/htmlhelp.xsl \
	 manual.xml
	cp $(LANG)/make_chm_index.html htmlhelp-$(LANG)/
	$(WINE) regsvr32 tools/hhw/it*.dll
	WINEDLLOVERRIDES="itircl,itss=n" wine tools/hhw/hhc.exe htmlhelp-en/htmlhelp.hhp || true
	cp htmlhelp-$(LANG)/htmlhelp.chm ./manual-$(LANG).chm
	rm -rf htmlhelp-$(LANG)

revcheck:
	$(PHP) -f scripts/revcheck.php $(LANG) > revcheck.html

clean:
	for x in $(LANGS_AVAIL); do rm -rf manual-$${x} manual-$${x}.fo manual-$${x}.pdf htmlweb-$${x}; done
	rm -f revcheck.html
