#!/bin/bash
# Requires: https://github.com/joewalnes/websocketd
# Chat server
# messages are logged to ROOMS_DIR/room.log file and user list are files inUSERS_DIR/room_username
#
# For proper functionality rotation of room logs is nessesary
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
# then apparmor needs permission for this program to write to BASE_DIR
#
# run with 
# PORT=8080
#/path/to/websocketd --port=${PORT} /path/to/chat_server.sh

# variables
BASE_DIR=/var/tmp/ws
ROOMS_DIR=${BASE_DIR}/rooms
USERS_DIR=${BASE_DIR}/users
LAST_MESSAGES=20

#functions
function givedate() {
  # returns date in choosen format
  echo "[`date "+%H:%M:%S"`]"
}

function roomUsers() {
  # returns this room user list sepated by comma
  echo `ls ${USERS_DIR} |grep ${ROOM} | cut -f2 -d"_" |tr "\n" ", "`
}

function lastAnonymousNumber() {
  # returns this room user list sepated by comma
  echo `ls ${USERS_DIR} |grep ${ROOM} | cut -f2 -d"_"  | cut -f2 -d"_" |grep "Anonymous" |cut -c10- |sort -n|tail -1`
}

# initialization
ROOM=`echo "${PATH_INFO}" |cut -f2 -d"/"`
ROOM_LOG=${ROOMS_DIR}/${ROOM}.log

USER=${REMOTE_USER} # this is set by apache or other web server, or can be modified to read PHP sessionid from cookie and read username from PHP session storage
if [ "${USER}" == "" ]; then
  LASTNUMBER=$(lastAnonymousNumber)
  if [ "${LASTNUMBER}" == "" ];then
    USER="Anonymous0"
  else
    USER="Anonymous`expr ${LASTNUMBER} + 1`"
  fi
fi

touch ${USERS_DIR}/${ROOM}_${USER}
echo "$(givedate) ${USER} joined the ${ROOM}" >> ${ROOM_LOG}
echo "$(givedate) Welcome to the chat room ${ROOM} ${USER}!"
echo "$(givedate) Userlist: $(roomUsers)" >> ${ROOM_LOG}

# logout
on_die() {
  echo "$(givedate) ${USER} quit the ${ROOM}" >> ${ROOM_LOG}
  rm ${USERS_DIR}/${ROOM}_${USER}
  echo "$(givedate) Userlist: $(roomUsers)" >> ${ROOM_LOG}
  exit 0
}

trap 'on_die' TERM SIGHUP SIGINT SIGTERM

# talking
tail -n ${LAST_MESSAGES} -f ${ROOM_LOG} --pid=$$ &
while read MSG; do
  if [ "`echo ${MSG} |cut -c1`" == "/" ]; then
    MSG=`python parse_command.py ${MSG}`
  fi
  echo "$(givedate) ${USER}> ${MSG}" >> ${ROOM_LOG}; 
done

