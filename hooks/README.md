# Creating a web hook

## Spark Bot example

In this example,
UISERVER = server that provides user interface (if any) 
BOTSERVER = server where the web hook is installed


### Requirements

* server running php

e.g.
  server = www.example.com
  
  H = /path/to/hook-handler
  
  
### install @ BOTSERVER

* from lib
SparkClient.php, HookHandler.php, SparkHookHandler

* from the hooks directory
copy and edit
  spark_example.php to  my_hook_hanlder.php
  config_inc.example.php config_inc.php

### install @ UISERVER
....

### create web hook
https://developer.ciscospark.com/webhooks-explained.html

* resource
all, messages, ...


MjM3YTg3NDQtYzRmYS00MzAxLWFkMTQtYzcxMDYzOWNmOWU5YjNmZThlZWMtZDk5

* messages/created

roomId, roomType, personId, personEmail, mentionedPeople, hasFiles


 





