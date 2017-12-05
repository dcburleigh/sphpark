

WEBROOT = 
LOCALURL = 
WEBDIR = 
LOGDIR = 

REPOROOT = $(g)

DELDEVLIB = 

# -- 
REMOTENAME = 
REMOTE = 
REMOTEPATH = 
REMOTEURL = 

include makefile.conf

ifeq ($REPO),)
REPO = $(REPOROOT)/sphpark
endif

ifeq ($(WEBDIR),)
WEBDIR = $(WEBROOT)/spark
endif

ifeq ($(DELDEVLIB),)
DELDEVLIB = $(REPOROOT)/webroot/deldev
endif


# ...................................................................................

#DELDEVFILES = $(DELDEVLIB)/Logger.php $(DELDEVLIB)/Errors.php
#DELDEVFILES = $(DELDEVLIB)/LoggerTrait.php $(DELDEVLIB)/Errors.php
DELDEVFILES = LoggerTrait.php Errors.php

DELDEVlocal = $(patsubst %,$(WEBDIR)/%,$(DELDEVFILES))

$(DELDEVlocal): $(WEBDIR)/%: $(DELDEVLIB)/%
	cp $< $@

deldevlib-local: $(DELDEVlocal)


# ...................................................................................
# remote



show-remote:
	echo remote $(REMOTE) name $(REMOTENAME)
	echo remote $(REMOTEURL)
	echo remote path $(REMOTEPATH)
	ssh $(REMOTE) ls -l $(REMOTEPATH)


upload-deldevlib: 
	rsync -av $(DELDEVFILES)  $(REMOTE):$(REMOTEPATH)

show-dd:
	@echo repo $(DELDEVLIB)
	@echo files $(DELDEVFILES)
	@echo local $(DELDEVlocal)




# ...................................................................................
COREFILES = config_inc.php auth.php getSparkAuth.php SparkApp.php
COREFILES = auth.php getSparkAuth.php SparkApp.php
COREFILES =  getSparkAuth.php SparkApp.php

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


TESTlocal = $(patsubst %,$(WEBDIR)/%,$(TFILES))
STlocal = $(patsubst %,$(WEBDIR)/%,$(STFILES))

LIBlocal = $(patsubst %,$(WEBDIR)/%,$(LIBFILES))
LIBsrc = $(patsubst %,$(REPO)/lib/%,$(LIBFILES))

HOOKFILES = hook_basic.php hook_example1.php
HOOKFILES = hook_basic.php  hook_box.php spark_example0.php spark_example.php

LIBlocal = $(patsubst %,$(WEBDIR)/%,$(LIBFILES))

HOOKlocal = $(patsubst %,$(WEBDIR)/%,$(HOOKFILES))

update-me: makefile

makefile: %: $(REPO)/build/%
	cp $< $@

#install-basic: $(WEBDIR) $(LOGDIR) install-lib  $(HOOKlocal)
install-basic: $(WEBDIR) $(LOGDIR) install-lib install-defaults

#install-defaults: $(WEBDIR)/config_inc.php $(WEBDIR)/.htaccess $(WEBDIR)/index.html
install-defaults: 
	@echo no-op

install-lib: $(WEBDIR) $(LIBlocal)

$(LIBlocal): $(WEBDIR)/%: $(REPO)/lib/%
	cp $< $@

show-local:
	ls -l $(WEBDIR) $(LOGDIR)


$(HOOKlocal): $(WEBDIR)/%: $(REPO)/hooks/%
	cp $< $@

install-hook: install-basic  $(HOOKlocal)

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

install-test: $(WEBDIR) install-lib $(TESTlocal) $(STlocal) $(WEBDIR)/config_inc.php

$(TESTlocal): $(WEBDIR)/%: $(REPO)/sample_code/%
	cp $< $@

$(STlocal): $(WEBDIR)/%: $(REPO)/styles/%
		cp $< $@

# ...................................................................................
install-test-auth:
	@echo TODO


install-ex1:
	@echo TODO


# ...................................................................................

#install-test-auth: $(WEBDIR) install-lib
#
# set up auth application on remote server
#
AUTHLIB =  lib/HookHandler.php  lib/SparkApp.php lib/AuthHandler.php

AUTHFILES = images/deldev_avatar.png  hooks/auth_handler.php build/config_inc.example.php
AUTHFILES = images/deldev_avatar.png  hooks/auth_handler.php
AUTHFILESlocal = $(patsubst %,$(REPO)/%,$(AUTHFILES) $(AUTHLIB))

upload-app-auth:  $(AUTHFILESlocal)
	rsync -av  $(AUTHFILESlocal) $(REMOTE):$(REMOTEPATH)

upload-app-config: config_inc.$(REMOTENAME).php
	rsync -av config_inc.$(REMOTENAME).php $(REMOTE):$(REMOTEPATH)/config_inc.php

install-app-config: config_inc.$(REMOTENAME).php
	cp config_inc.$(REMOTENAME).php $(WEBDIR)/config_inc.php


APPFILESlocal = $(WEBDIR)/test_auth.php

$(APPFILESlocal): $(WEBDIR)/%:  $(REPO)/sample_code/%
	cp $< $@

install-app-local: install-app-config  install-basic $(APPFILESlocal)  deldevlib-local


#
# upload to remote server
#
upload-core: $(COREFILES)
	rsync -av  $(COREFILES) $(REMOTE):$(REMOTEPATH)

#install-remote-app: upload-core upload-app-auth upload-app-config
install-remote-app: upload-app-auth upload-app-config



# ...................................................................................

upload-hook: $(HFILES)
	rsync -av  $(COREFILES) $(REMOTE):$(REMOTEPATH)


test-hook-handler:
	curl $(REMOTEURL)/hook_test.php


test0:
	curl '$(LOCALURL)/hook_basic.php'

test1:
	curl '$(LOCALURL)/spark_example1.php?item=1'

perl-mods: BINC  BINC/Spark.pm BINC/Error.pm

BINC:
	mkdir $@


BINC/Spark.pm: BINC/%: $(REPO)/spark-perl/lib/BINC/%
	cp $< $@
