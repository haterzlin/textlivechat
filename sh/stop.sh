PID=`ps -ef |grep websocketd |grep www-data |cut -f2 -d" "|head -1`
kill -9 ${PID}
find /opt/chat/users/ -type f -exec rm {} \;

