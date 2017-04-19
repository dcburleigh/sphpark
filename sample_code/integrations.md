# install integration

* see
https://developer.ciscospark.com/authentication.html

## create

### overview

remote-auth: the server hosting the redirect URL for authentication

app-sever: the server hosting the application (maybe different from remote auth)

local = your development server, 
  with the git repo installed
  and SSH access to the remote-auth server, and the app-server
 

### remote server

set up SSH keys to remote 
create remote path


### build directory

edit makefile.conf
* remote server

edit config_inc.NAME.php, using parameters from the 'create integration' page
* 

### Install 
make install-basic  install-app-config install-app-local

edit config_inc.php

make install-remote-app

## Run

goto <LocalURL>

click

sign in



