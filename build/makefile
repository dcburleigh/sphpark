

REPO = /home/darin/git

REMOTE = osmazome@osmazome.org
REMOTEPATH = /home/osmazome/public_html/silurian/spark
REMOTEURL = http://www.silurian.org/spark

REMOTE = utiluser@filedepot.cdw.com
REMOTEPATH = /srv/filedepot/bots/spark

WEBDIR = /home/darin/git/silurian/spark


include makefile.conf

COREFILES = config_inc.php auth.php getSparkAuth.php SparkApp.php

SPFILES = $(COREFILES) index.html

TFILES = $(COREFILES)  test.php test_post.php hook_test.php 

HFILES = $(COREFILES) HookHandler.php hook_test.php 

LIBFILES =  HookHandler.php  SparkHookHandler.php SparkClient.php

HOOKFILES = hook_basic.php

LIBlocal = $(patsubst %,$(WEBDIR)/%,$(LIBFILES))
LIBsrc = $(patsubst %,$(REPO)/sphpark/lib/%,$(LIBFILES))

update-me: makefile

makefile: %: $(REPO)/sphpark/build/%
	cp $< $@

install-basic: install-lib 

install-lib: $(LIBlocal)

$(LIBlocal): $(WEBDIR)/%: $(REPO)/sphpark/lib/%
	cp $< $@



upload-core: $(COREFILES)
#	scp $(COREFILES) $(REMOTE):$(REMOTEPATH)
	rsync -av  $(COREFILES) $(REMOTE):$(REMOTEPATH)


upload-hook: $(HFILES)
	rsync -av  $(COREFILES) $(REMOTE):$(REMOTEPATH)

test-hook-handler: 
	curl $(REMOTEURL)/hook_test.php


test1: 
	curl 'http://localhost/~engineer/spark/hook_test.php?item=1'
	