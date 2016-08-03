

REPO = /home/darin/git

REMOTEPATH = /home/osmazome/public_html/silurian/spark
REMOTEURL = http://www.silurian.org/spark

REMOTE = utiluser@filedepot.cdw.com
REMOTEPATH = /srv/filedepot/bots/spark

WEBDIR = /home/darin/git/silurian/spark

include makefile.conf

COREFILES = config_inc.php auth.php getSparkAuth.php SparkApp.php
COREFILES = auth.php getSparkAuth.php SparkApp.php

SPFILES = $(COREFILES) index.html

TFILES = $(COREFILES)  test.php test_post.php hook_test.php 

HFILES = $(COREFILES) HookHandler.php hook_test.php 
HFILES = SparhHookHandler.php HookHandler.php hook_test.php 

LIBFILES =  HookHandler.php  SparkHookHandler.php SparkClient.php

HOOKFILES = hook_basic.php  hook_box.php spark_example0.php

LIBlocal = $(patsubst %,$(WEBDIR)/%,$(LIBFILES))

HOOKlocal = $(patsubst %,$(WEBDIR)/%,$(HOOKFILES))

update-me: makefile

makefile: %: $(REPO)/sphpark/build/%
	cp $< $@

install-basic: install-lib $(HOOKlocal)

install-lib: $(LIBlocal)

#install1: $(WEBDIR)/hook_basic.php

$(LIBlocal): $(WEBDIR)/%: $(REPO)/sphpark/lib/%
	cp $< $@

$(HOOKlocal): $(WEBDIR)/%: $(REPO)/sphpark/hooks/%
	cp $< $@

show-remote:
	echo remote $(REMOTE)
	echo remote $(REMOTEURL)
	echo remote path $(REMOTEPATH)

LIBsrc = $(patsubst %,$(REPO)/sphpark/lib/%,$(LIBFILES))
HOOKsrc = $(patsubst %,$(REPO)/sphpark/hooks/%,$(HOOKFILES))

upload-basic:
	rsync -av  $(LIBsrc) $(HOOKsrc) $(REMOTE):$(REMOTEPATH)


upload-core: $(COREFILES)
#	scp $(COREFILES) $(REMOTE):$(REMOTEPATH)
	rsync -av  $(COREFILES) $(REMOTE):$(REMOTEPATH)


upload-hook: $(HFILES)
	rsync -av  $(COREFILES) $(REMOTE):$(REMOTEPATH)

test-hook-handler: 
	curl $(REMOTEURL)/hook_test.php


test1: 
	curl 'http://localhost/~engineer/spark/hook_test.php?item=1'

perl-mods: BINC  BINC/Spark.pm BINC/Erro.pm

BINC:
	mkdir $@


BINC/Spark.pm: BINC/%: $(REPO)/spark-perl/lib/BINC/%
	cp $< $@
