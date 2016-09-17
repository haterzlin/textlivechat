#!/bin/bash
# Requires: https://github.com/joewalnes/websocketd

# variables
CONFIG_FILE=/etc/opt/websocketd_with_webrtc_chat.ini
export LANG=`grep LANG $CONFIG_FILE |head -1 |cut -f2 -d"="`
BASE_DIR=`grep BASE_DIR $CONFIG_FILE |head -1 |cut -f2 -d"="`
ROOMS_DIR=${BASE_DIR}/rooms
USERS_DIR=${BASE_DIR}/users
SESSIONS_DIR=${BASE_DIR}/sessions
AUTH_LOG_FILE=${BASE_DIR}/log/authentization.log
LAST_MESSAGES=20

#functions
function givedate() {
  # returns date in choosen format
  echo "[`date "+%H:%M:%S"`]"
}

function roomUsers() {
  # returns this room user list sepated by comma
  #echo `ls ${USERS_DIR} |grep ${ROOM} | cut -f2 -d"_" |tr "\n" ", "`
  for USER in $(ls ${USERS_DIR} |grep "^${ROOM}_"); do
    printf `echo $USER| cut -f2 -d"_"`:`cat ${USERS_DIR}/$USER`, ;
  done;
}

function lastUserNumber() {
  # returns number of last user $1
  CHARS=`echo -n ${1} |wc -c`
  CHARS=$((${CHARS} + 1))
  echo `ls ${USERS_DIR} |grep "^${ROOM}_" | cut -f2 -d"_"  | cut -f2 -d"_" |grep "${1}" |cut -c${CHARS}- |sort -n|tail -1`
}

# initialization
ROOM=`echo "${PATH_INFO}" |cut -f2 -d"/"|tr " " "-"|tr "_" "-"`
ROOM_LOG=${ROOMS_DIR}/${ROOM}.log

#COOKIES=`env|grep "HTTP_COOKIE"`
#USER=`python3 ${BASE_DIR}/py/authenticate.py "${COOKIES}"`
SESSION_FILE=`env|grep "HTTP_COOKIE"|cut -f2- -d"="|tr ';' '\n'|grep PHPSESS |tail -1 |cut -f2 -d"="`
#env|grep -i cookie >> $AUTH_LOG_FILE
#env|grep "HTTP_COOKIE"|tr ';' '\n'|grep PHPSESS >> $AUTH_LOG_FILE
#ls -la ${SESSIONS_DIR}/sess_${SESSION_FILE} >> $AUTH_LOG_FILE
#cat ${SESSIONS_DIR}/sess_${SESSION_FILE} >> $AUTH_LOG_FILE
USER=`cat ${SESSIONS_DIR}/sess_${SESSION_FILE} | cut -f2 -d'"'`
if [ "${USER}" == "" ]; then
  USER="Anonymous:Gray"
fi
COLOR=`echo ${USER} | cut -f2 -d":"`
USER=`echo ${USER} | cut -f1 -d":"`
if [[ $(roomUsers) == *"${USER}"* ]]; then
  LASTNUMBER=$(lastUserNumber ${USER})
  if [ "${LASTNUMBER}" == "" ];then
    USER="${USER}1"
  else
    USER="${USER}`expr ${LASTNUMBER} + 1`"
  fi
fi

echo `date +"%Y-%m-%d_%H:%M:%S"` ${HTTP_X_FORWARDED_FOR} ${USER} >> ${AUTH_LOG_FILE}

echo "${COLOR}" > ${USERS_DIR}/${ROOM}_${USER} # create file and send user color to it
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

trap 'on_die' 0 SIGHUP SIGINT SIGQUIT SIGILL SIGABRT SIGFPE SIGTERM

# talking
tail -n ${LAST_MESSAGES} -f ${ROOM_LOG} --pid=$$ &
while read MSG; do
  if [ "`echo ${MSG} |cut -f1 -d' '`" == "/color" ]; then
    echo `echo ${MSG} |cut -f2 -d' '` > ${USERS_DIR}/${ROOM}_${USER}
    echo "$(givedate) Userlist: $(roomUsers)" >> ${ROOM_LOG}
  fi
  if [ "`echo ${MSG} |cut -c1`" == "/" ]; then
    MSG=`python3 ${BASE_DIR}/py/parse_command.py ${MSG}`
  fi
  echo "$(givedate) ${USER}> ${MSG}" >> ${ROOM_LOG};
done

