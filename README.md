# SPHpark

PHP client for Spark (and related cloud bots)

Definitions:
REPO = path/to/this/repo
REMOTE = the server where the code is installed
WEBDIR = path/to/local-docroot

## Install

* configure: build directory

set up SSH keys with REMOTE server

copy REPO/build/makefile .

edit makefile.conf

* configure: local web directory

make install-basic

edit WEBDIR/config_inc.php

make install-test

* configure: remote auth HookHandler

### example: Spark webhook endpoint

* install
make install-hook

* configure
edit config_inc.php

If your web hook was created with a secret, and you want to validate the signature,
set the $webhook_secrt in config_inc.php.





## Configure

### build dir
