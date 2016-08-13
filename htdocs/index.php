<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Websocketd chat with webrtc</title>
    <link rel="stylesheet" type="text/css" href="theme.css">
    <script src="functions.js" type="text/javascript"></script>
  </head>
  <body>

<!-- 
Working with websocked chat_server, ws_url needs to be properly set, so client can go to
living instance of chat server.
Variable number of old messages can be set, to view more history.
User name and room name is set by http parameters user and room
-->
    <div id="userlist"></div>
    <div id="RTCmessage"></div>
    <div id="messages"></div>
    <form onsubmit="send();return false;" onKeyUp="sendToRTCPeers();">
        Message <input type="text" id="messageinput" autofocus="autofocus" list="usernames" autocomplete="off">
        <input type="submit" value="Send" id="sendButton">
        <a title="Insert image" href="#" onclick="insert_image()"><img title="Insert image" src="icos/image2.svg" height="25px"></a>
        <a title="Insert link" href="#" onclick="insert_link()"><img title="Insert link" src="icos/link1.svg" height="25px"></a>
        <a title="Throw dice" href="#" onclick="document.getElementById('diceform').style.display='block'"><img title="Throw dice" src="icos/dice4.png" height="25px"></a>
        <input title="Choose color" id="colorpicker" type="color" onChange="ws.send('/color ' + this.value)">
        <a title="Disconnect" href="#" onclick="ws.close();"><img title="Disconnect" src="icos/exit.svg" height="25px" id="disconnecticon"></a>
    </form>
    <datalist id="usernames">
        <select name="usernames">
        </select>
    </datalist>
    <div id="diceform" style="display: none">
        <h4>Throw the dice!</h4>
        <div id="closediceform"><a onclick="document.getElementById('diceform').style.display='none';" style="cursor: pointer">x</a></div>
        <form onsubmit="throw_dice();return false;">
            <table>
            <tr><th><label for="dicecount">dice count: </label></th> <td><input type="text" id="dicecount" value="1"></td></tr>
            <tr><th><label for="dicesides">dice sides: </label></th> <td><input type="text" id="dicesides" value="6"></td></tr>
            <tr><th><label for="dicerepeat">repeat value: </label></th> <td><input type="text" id="dicerepeat" value="6"></td></tr>
            <tr><th></th><td><input type="submit" value="Throw"></td></tr>
            </table>
        </form>
    </div>

<?php
$config=parse_ini_file("/app/iwad/conf/websocket_chat_server.ini", true);
?>
    <script type="text/javascript">
    var ice_url = '<?php echo $config["global"]["ICE_SERVER_DNS"]; ?>';
    var ws_url = '<?php echo $config["global"]["WEBSOCKETD_SERVER_URL"]; ?>' + get_http_param('room');
    var ws = new WebSocket(ws_url);
    var number_of_old_messages = 15;
    var last_message_id = 1;
    var userListKeyword = "Userlist: ";
    var welcomeMessage = "Welcome to the chat room";
    var username = ""; // will be updated after welcomemessage
    var userColors = new Array();
    var ignoreList = new Array();

    // javascript pro různé browsery
    var RTCPeerConnection = window.RTCPeerConnection ||window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
    var RTCIceCandidate = window.RTCIceCandidate || window.mozRTCIceCandidate || window.RTCIceCandidate;
    var RTCSessionDescription =  window.RTCSessionDescription || window.mozRTCSessionDescription;
    navigator.getUserMedia = navigator.getUserMedia || navigator.mozGetUserMedia || navigator.webkitGetUserMedia;
    var MyRTCConfiguration = { "iceServers": [{ "urls":"stun:" + ice_url }] };
    var CurrentPeerConnections = new Array();
    var CurrentRTCDataChannels = new Array();

    ws.onopen = function() {
        document.getElementById('sendButton').disabled=false;
        document.getElementById('messageinput').disabled=false;
    }
    ws.onclose = function() {
        document.getElementById('sendButton').disabled=true;
        document.getElementById('messageinput').disabled=true;
        display_received_message("Connection closed.");
     };
    ws.onerror = function() {
         display_received_message("error: " + event.error);
    };
    ws.onmessage = function(event) {
        if (event.data.indexOf(userListKeyword) > -1) {
            updateUserList(event.data);
        }
        else {
            if (event.data.indexOf(welcomeMessage) > -1 & last_message_id == 1) {
                username=event.data.split(" ").pop().slice(0, -1);
                console.log("Username is " + username);
                setTimeout("sendOffers(getUserList())", 500); // after successful connecting to websocket and update user list, we can connect to RTC
            }
            if (event.data.indexOf('"type":"offer","sdp"') > -1 && event.data.indexOf('> ' + username + ': ') > -1) {
                sendAnswerToOffer(event.data);
            }
            if (event.data.indexOf('"candidate":"candidate') > -1 && event.data.indexOf('> ' + username + ': ') > -1) {
                receivedIceCandidate(event.data);
            }
            if (event.data.indexOf('"type":"answer","sdp"') > -1 && event.data.indexOf('> ' + username + ': ') > -1) {
                receivedAnswerToOffer(event.data);
            }
            if ((event.data.indexOf('"type":"answer","sdp"') == -1) & (event.data.indexOf('"type":"offer","sdp"') == -1) & (event.data.indexOf('"candidate":"candidate')  == -1)) {
                words = event.data.split(" ");
                whisperto = words[2].slice(0,-1);
                from = words[1].slice(0,-1);
                if (getUserList().indexOf(whisperto) != -1) {
                    if ((whisperto == username || from == username) & ignoreList.indexOf(from) == -1) {
                        display_received_message(event.data);
                    }
                }
                else {
                    if (ignoreList.indexOf(from) ==  -1 ) {
                        display_received_message(event.data);
                    }
                }
            }
        }
    };

    </script>
  </body>
</html>
