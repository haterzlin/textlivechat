Requires: https://github.com/joewalnes/websocketd
Websocket and Webrtc text chat server
=
Lubomir Mlich <mlich.lubomir@gmail.com>
2016-09-12

Main script is written in bash at sh/chat_server.sh

messages are logged to ROOMS_DIR/room.log file and user list are files in USERS_DIR/room_username

For proper functionality rotation of room logs is nessesary

logrotate example
cat /etc/logrotate.d/websocketd_chat_server
/var/log/websocketd/rooms/*.log {
   daily
   missingok
   nocompress
   notifempty
   copytruncate
   dateext
   maxage 7
}

then apparmor needs permission for this program to write to 
$BASE_DIR/rooms 
$BASE_DIR/users
$BASE_DIR/sessions

run chat server without SSL as root: 
sudo -u www-data /path/to/bin/websocketd --address=127.0.0.1 --port=8089 bash /path/to/sh/chat_server.sh >/path/to/log/websocketd_chat.log &

run chat server with SSL as root:
sudo -u www-data /path/to/bin/websocketd --address=127.0.0.1 --port=8089 --ssl --sslkey=/path/to/.ssl/key.pem --sslcert=/path/to/.ssl/cert.pem bash /path/to/sh/chat_server.sh >/path/to/log/websocketd_chat.log &

apache2-websocket redirect via proxy_wstunnel mod (and ssl mod if you want ssl)

ProxyPass "/chat/rooms/" "wss://localhost:8089/"

Stun server is needed for ice candidates to enable webrtc.

Config
==
Review config file at conf/websocket_chat_server.ini

User authentication
==
Users enter their name to PHP form, that saves it to session, and chat servers check session file based on PHPSESSIONID cookie
Alternatively you can choose to use OpenSSO authentication using py/authenticate.py
Non-authenticaticated users will become Anonymous and if someone have the same name, number is added to his name: eg. Anonymous1

Other files
==
py/parse_command.py - defines chat room commands as /dice or /color

htdocs
==
index.php - establishes websocket and webrtc connection to room defined by get parameter room and show user interave
functions.php - loaded by index.php, it contains all extensive javasript function to make websocket and webrtc connections availaible
room_create.php - simple user interface for entering room name redirecting to index.php
room_history.php - user can read room chat record
room_list_historical.php - user can view list of historical room records
room_list.php - user can view list of rooms with users or stable rooms defined in config file
set_username.php - user can set his username in simple form which will be saved to his session
theme.css - cascading style sheet
