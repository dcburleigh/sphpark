

REPOROOT = /home/darin/git

REMOTE = osmazome@osmazome.org
REMOTEPATH = /home/osmazome/public_html/silurian/spark
REMOTEURL = http://www.silurian.org/spark

REMOTE = utiluser@filedepot.cdw.com
REMOTEPATH = /srv/filedepot/bots/spark

#
# local web
#
WEBDIR = /home/darin/git/silurian/spark
LOGDIR = /home/darin/git/silurian/log
LOCALURL = http://localhost/~engineer/spark/

include makefile.conf

COREFILES = config_inc.php auth.php getSparkAuth.php SparkApp.php

SPFILES = $(COREFILES) index.html

TFILES = $(COREFILES)  test.php test_post.php hook_test.php
TFILES =  index.php listRooms.php test_auth.php
#install-test-auth: TFILES = test_auth.php

HFILES = $(COREFILES) HookHandler.php hook_test.php

LIBFILES =  HookHandler.php  SparkHookHandler.php SparkClient.php SparkApp.php AuthHandler.php

HOOKFILES = hook_basic.php hook_example1.php

TESTlocal = $(patsubst %,$(WEBDIR)/%,$(TFILES))

LIBlocal = $(patsubst %,$(WEBDIR)/%,$(LIBFILES))
LIBsrc = $(patsubst %,$(REPOROOT)/sphpark/lib/%,$(LIBFILES))

update-me: makefile

makefile: %: $(REPOROOT)/sphpark/build/%
	cp $< $@

#
# install on local web server
#

HOOKlocal = $(patsubst %,$(WEBDIR)/%,$(HOOKFILES))

install-basic: $(WEBDIR) $(LOGDIR) install-lib  $(HOOKlocal)

install-lib: $(WEBDIR) $(LIBlocal)

$(LIBlocal): $(WEBDIR)/%: $(REPOROOT)/sphpark/lib/%
	cp $< $@

$(HOOKlocal): $(WEBDIR)/%: $(REPOROOT)/sphpark/hooks/%
	cp $< $@

$(WEBDIR):
	mkdir $@
	@echo set permissions on $(WEBDIR)

$(LOGDIR):
	mkdir $@
	@echo set permissions on $@
	chmod 777 $(LOGDIR)


install-test-auth install-test: $(WEBDIR) install-lib $(TESTlocal) $(WEBDIR)/config_inc.php

$(TESTlocal): $(WEBDIR)/%: $(REPOROOT)/sphpark/sample_code/%
	cp $< $@

#install-test-auth: $(WEBDIR) install-lib
#
# set up auth application on remote server
#
AUTHLIB =  lib/HookHandler.php  lib/SparkApp.php lib/AuthHandler.php

AUTHFILES = images/deldev_avatar.png  hooks/auth_handler.php build/config_inc.example.php
AUTHFILESlocal = $(patsubst %,$(REPOROOT)/sphpark/%,$(AUTHFILES) $(AUTHLIB))

upload-app-auth:  $(AUTHFILESlocal)
	rsync -av  $(AUTHFILESlocal) $(REMOTE):$(REMOTEPATH)

upload-app-config:
	rsync -av config_inc.SIL.php $(REMOTE):$(REMOTEPATH)/config_inc.php


show-remote:
	ssh $(REMOTE) ls -l $(REMOTEPATH)

#
# upload to remote server
#
upload-core: $(COREFILES)
#	scp $(COREFILES) $(REMOTE):$(REMOTEPATH)
	rsync -av  $(COREFILES) $(REMOTE):$(REMOTEPATH)


upload-hook: $(HFILES)
	rsync -av  $(COREFILES) $(REMOTE):$(REMOTEPATH)

test-hook-handler:
	curl $(REMOTEURL)/hook_test.php


test0:
	curl '$(LOCALURL)/hook_basic.php'

test1:
	curl '$(LOCALURL)/spark_example1.php?item=1'
