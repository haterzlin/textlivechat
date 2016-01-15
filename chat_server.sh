#!/bin/bash
# Requires: https://github.com/joewalnes/websocketd
# Chat server, for proper functionality rotation of room logs is nessesary
#
# logrotate example
# cat /etc/logrotate.d/websocketd_chat_server
# /var/log/websocketd/rooms/*.log {
#   daily
#   missingok
#   rotate 7
#   compress
#   delaycompress
#   notifempty
#   copytruncate
# }
#
# then apparmor needs permission for this program to write to rooms log directory
#
# run with 
# PORT=8080
#/path/to/websocketd --port=${PORT} /path/to/chat_server.sh

# variables
ROOMS_DIR=/var/tmp/ws/rooms
LAST_MESSAGES=5

# initialization
USER=${REMOTE_USER} # this is set by apache or other web server, or can be modified to read PHP sessionid from cookie and read username form PHP session storage
if [ "${USER}" == "" ]; then
  USER="Anonymous"
fi
ROOM=`echo "${PATH_INFO}" |cut -f2 -d"/"`
ROOM_LOG=${ROOMS_DIR}/${ROOM}.log
ROOM_USERS=${ROOMS_DIR}/${ROOM}.users
echo ${USER} >> ${ROOM_USERS}
echo "[$(date +%H:%M:%S)] ${USER} joined the ${ROOM}" >> ${ROOM_LOG}
echo "[$(date +%H:%M:%S)] Welcome to the chat room ${ROOM} ${USER}!"

# logout
on_die() {
  echo "[$(date +%H:%M:%S)] ${USER} quit the ${ROOM}" >> ${ROOM_LOG}
  grep -v ${USER} ${ROOM_USERS} >>/tmp/chat$$
  mv /tmp/chat$$ ${ROOM_USERS}
  exit 0
}

trap 'on_die' TERM SIGHUP SIGINT SIGTERM

# talking
tail -n ${LAST_MESSAGES} -f ${ROOM_LOG} --pid=$$ &
while read MSG; do
  if [ "`echo ${MSG} |cut -c1`" == "/" ]; then
    if [ "${MSG}" == "/getusers" ]; then
        MSG="/getusers ${ROOM_USERS}"
    fi
    MSG=`python parse_command.py ${MSG}`
  fi
  echo "[$(date +%H:%M:%S)] ${USER}> ${MSG}" >> ${ROOM_LOG}; 
done

