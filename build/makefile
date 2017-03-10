

WEBROOT = /Users/engineer/Sites/apps
LOCALURL = http://localhost/~engineer/spark/

REPOROOT = /home/darin/git

REMOTEPATH = /home/osmazome/public_html/silurian/spark
REMOTEURL = http://www.silurian.org/spark
REMOTE = osmazome@osmazome.org

# -- 
REMOTE = utiluser@filedepot.cdw.com
REMOTEPATH = /srv/filedepot/bots/spark
REMOTEURL = https://www.filedepot.cde.com/spark

WEBDIR = /home/darin/git/silurian/spark

LOGDIR = /home/darin/git/silurian/log
LOCALURL = http://localhost/~engineer/spark/

REPOROOT = $(g)

REMOTE = utiluser@filedepot.cdw.com
REMOTEPATH = /srv/filedepot/bots/spark
REMOTEURL = https://filedepot.cdw.com/bots/spark

#
# local web
#
WEBDIR = 

# ???
LOGDIR = 

include makefile.conf

ifeq ($REPO),)
REPO = $(REPOROOT)/sphpark
endif

ifeq ($(WEBDIR),)
WEBDIR = $(WEBROOT)/spark
endif

COREFILES = config_inc.php auth.php getSparkAuth.php SparkApp.php
COREFILES = auth.php getSparkAuth.php SparkApp.php

SPFILES = $(COREFILES) index.html

# sample code for testing core functionality
TFILES = $(COREFILES)  test.php test_post.php hook_test.php
TFILES =  index.php listRooms.php test_auth.php showThread.php
TFILES =  index.php listRooms.php listPeople.php postMessage.php  showThread.php spark_example.css
#install-test-auth: TFILES = test_auth.php

STFILES = sparkui.css

HFILES = $(COREFILES) HookHandler.php hook_test.php

LIBFILES =  HookHandler.php  SparkHookHandler.php SparkClient.php SparkApp.php AuthHandler.php SparkThread.php

HFILES = $(COREFILES) HookHandler.php hook_test.php
HFILES = SparkHookHandler.php HookHandler.php hook_test.php

HOOKFILES = hook_basic.php hook_example1.php

TESTlocal = $(patsubst %,$(WEBDIR)/%,$(TFILES))
STlocal = $(patsubst %,$(WEBDIR)/%,$(STFILES))

LIBlocal = $(patsubst %,$(WEBDIR)/%,$(LIBFILES))
LIBsrc = $(patsubst %,$(REPO)/lib/%,$(LIBFILES))

HOOKFILES = hook_basic.php  hook_box.php spark_example0.php

LIBlocal = $(patsubst %,$(WEBDIR)/%,$(LIBFILES))

HOOKlocal = $(patsubst %,$(WEBDIR)/%,$(HOOKFILES))

update-me: makefile

makefile: %: $(REPO)/build/%
	cp $< $@

install-basic: $(WEBDIR) $(LOGDIR) install-lib  $(HOOKlocal)

install-lib: $(WEBDIR) $(LIBlocal)

$(LIBlocal): $(WEBDIR)/%: $(REPO)/lib/%
	cp $< $@

show-local:
	ls -l $(WEBDIR)


$(HOOKlocal): $(WEBDIR)/%: $(REPO)/hooks/%
	cp $< $@

show-remote:
	echo remote $(REMOTE)
	echo remote $(REMOTEURL)
	echo remote path $(REMOTEPATH)
	ssh $(REMOTE) ls -l $(REMOTEPATH)

LIBsrc = $(patsubst %,$(REPO)/sphpark/lib/%,$(LIBFILES))
HOOKsrc = $(patsubst %,$(REPO)/sphpark/hooks/%,$(HOOKFILES))

upload-basic:
	rsync -av  $(LIBsrc) $(HOOKsrc) $(REMOTE):$(REMOTEPATH)

$(WEBDIR):
	mkdir $@
	@echo set permissions on $(WEBDIR)

$(LOGDIR):
	mkdir $@
	@echo set permissions on $@
	chmod 777 $(LOGDIR)


COMMONDIR = $(REPOROOT)/webroot

#COMMONFILES = deldev/utils.php
#COMMONFILESlocal = $(patsubst %,$(WEBDIR)/%,$(COMMONFILES))
COMMONFILESlocal = $(WEBDIR)/utils.php $(WEBDIR)/deldev.css

test2: 
	echo trunk $(REPOROOT)
	echo REPO $(REPO)
	echo WEB $(WEBDIR)


install-common: $(COMMONFILESlocal) 

$(WEBDIR)/utils.php: $(WEBDIR)/%: $(COMMONDIR)/deldev/%
	cp $< $@

$(WEBDIR)/deldev.css: $(WEBDIR)/%: $(COMMONDIR)/styles/%
	cp $< $@

install-test-auth install-test: $(WEBDIR) install-lib $(TESTlocal) $(STlocal) $(WEBDIR)/config_inc.php

$(TESTlocal): $(WEBDIR)/%: $(REPO)/sample_code/%
	cp $< $@

$(STlocal): $(WEBDIR)/%: $(REPO)/styles/%
		cp $< $@

#install-test-auth: $(WEBDIR) install-lib
#
# set up auth application on remote server
#
AUTHLIB =  lib/HookHandler.php  lib/SparkApp.php lib/AuthHandler.php

AUTHFILES = images/deldev_avatar.png  hooks/auth_handler.php build/config_inc.example.php
AUTHFILESlocal = $(patsubst %,$(REPO)/%,$(AUTHFILES) $(AUTHLIB))

upload-app-auth:  $(AUTHFILESlocal)
	rsync -av  $(AUTHFILESlocal) $(REMOTE):$(REMOTEPATH)

upload-app-config:
	rsync -av config_inc.SIL.php $(REMOTE):$(REMOTEPATH)/config_inc.php


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

perl-mods: BINC  BINC/Spark.pm BINC/Erro.pm

BINC:
	mkdir $@


BINC/Spark.pm: BINC/%: $(REPO)/spark-perl/lib/BINC/%
	cp $< $@
