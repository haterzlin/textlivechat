/opt/chat/bin/websocketd --address=127.0.0.1 --port=8089 --ssl --sslkey=/opt/chat/.ssl/chat.key --sslcert=/opt/chat/.ssl/chat.pem bash /opt/chat/sh/chat_server.sh 1>/opt/chat/log/websocketd.log 2>&1

