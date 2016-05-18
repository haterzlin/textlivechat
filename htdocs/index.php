<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Websocketd chat with webrtc</title>
    <style>
html, body { height: 100%; margin: 0; padding-left: 0.5em; padding-top: 0.5em; background: #faebd7; color: black;}
#messages { height: 90%;overflow: auto;}
#userlist { width: 11em; position: absolute;  bottom: 1px; right: 1px; border: 1px dashed gray; padding: 1em; background: #faebd7;}
#userlist ul {list-style-type: none; padding-left: 0px;}
#RTCmessage {background-color: lightyellow;}
#sendButton { width: 6em; }
#diceform {position: absolute; bottom: 10%; right: 45%; margin: 1em; padding: 1em; border: 1px dashed grey; width: 13em; background: #faebd7}
#diceform input {width: 6em;}
#closediceform {position: absolute; top: 0px; right: 3px;}
#colorpicker {width: 28px; height: 24px; margin: 1px; padding: 1px; cursor: pointer;}
img {vertical-align: bottom;}
#disconnecticon {margin-bottom: 1px;}
    </style>
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
        <input id="colorpicker" type="color" onChange="ws.send('/color ' + this.value)">
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

    // functions for user list

    function updateUserList(userdata) {
        /***
           sets two lists, userlist for display purposes and usernames to make writing to users easy
           additionaly it works with ignore list
        ***/
        console.log("updating user list " + userdata);
        result = '<select name="usernames">\n';
        result2 = '<h4>User List</h4>\n<ul>\n';
        userdata = userdata.slice(userdata.indexOf(userListKeyword) + userListKeyword.length)
        users = userdata.split(",");
        for (index = 0, len = users.length; index < len; ++index) {
            currentuser = users[index].split(":")[0];
            currentusercolor = users[index].split(":")[1];
            if (currentusercolor) {
                currentusercolor = currentusercolor.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }
            else {
                currentusercolor = "black";
            }
            if (currentuser != "") {
                if (currentuser != username) {
                    result = result + '<option value="' + currentuser + ': "></option>\n';
                    ignoreIndex = ignoreList.indexOf(currentuser);
                    if (ignoreIndex == -1) {
                        result2 = result2 + '<li style="color:  + currentusercolor + " ><a title="Ignore ' + currentuser + '" id="ignore' + currentuser + 'link" href="#" onclick="ignoreUser(\'' + currentuser + '\');"><img title="Ignore User" src="icos/ok.svg" height="25px"></a> <span>' + currentuser + '</span>\n';
                    }
                    else {
                        result2 = result2 + '<a title="Don\'t ignore ' + currentuser + '" id="ignore' + currentuser + 'link" href="#" onclick="dontIgnoreUser(' + currentuser + ');"><img title="Don\'t ignore user" src="icos/Righthand.svg" height="25px"></a> <li style="color: ' + currentusercolor + ' " ><span>' + currentuser + '</span>\n';
                    }
                }
                userColors[currentuser] = currentusercolor;
            }
        }
        result = result + "</select>\n";
        result2 = result2 + "</ul>\n";
        document.getElementById("usernames").innerHTML = result;
        document.getElementById("userlist").innerHTML = result2;
    }

    function getUserList() {
        userscollection = document.getElementById('userlist').getElementsByTagName('li');
        users = [];
        for (var i = 0; i < userscollection.length; i++) 
            users.push(userscollection[i].children[1].innerHTML.trim());
        return users;
    }

    function ignoreUser(user) {
        ignoreList.push(user);
        ignorelink = document.getElementById("ignore"+user+"link");
        ignorelink.title="Don't ignore " + user;
        ignorelink.onclick = function() { dontIgnoreUser(user); };
        ignorelink.innerHTML = '<img title="Don\'t ignore User" src="icos/Righthand.svg" height="25px">';
        console.log("ignoring " + user);
        return false;
    }
    
    function dontIgnoreUser(user) {
        ignoreList.splice(ignoreList.indexOf(user), 1);
        ignorelink = document.getElementById("ignore"+user+"link");
        ignorelink.title="Ignore " + user;
        ignorelink.onclick = function() { ignoreUser(user); };
        ignorelink.innerHTML = '<img title="Ignore User" src="icos/ok.svg" height="25px">';
        console.log("not ignoring " + user);
        return false;
    }

    function get_http_param(name) {
        if(name=(new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search))
            return decodeURIComponent(name[1]);
    }

    function send() {
         ws.send(document.getElementById("messageinput").value);
         document.getElementById("messageinput").value = "";
         document.getElementById("sendButton").value = "Send";
    }

     function convert_images_and_links(input) {
         while (input.indexOf("{link}") != -1 || input.indexOf("{image}") != -1) {
             if (input.indexOf("{link}") >= 0) {
                 startindex = input.indexOf("{link}");
                 secondindex = input.indexOf(" ", startindex);
                 if (secondindex == -1) {
                     secondindex = input.length;
                 }
                 firstpart = input.substring(0,startindex);
                 secondpart = "<a target='_blank' href='" + input.substring(startindex+6, secondindex) + "'>link</a>";
                 thirdpart = input.substring(secondindex);
                 input = firstpart + secondpart + thirdpart;
             }
             if (input.indexOf("{image}") >= 0) {
                 startindex = input.indexOf("{image}");
                 secondindex = input.indexOf(" ", startindex);
                 if (secondindex == -1) {
                     secondindex = input.length;
                 }
                 firstpart = input.substring(0,startindex);
                 secondpart = "<img src='" + input.substring(startindex+7, secondindex) + "'>";
                 thirdpart = input.substring(secondindex);
                 input = firstpart + secondpart + thirdpart;
             }
         }
         console.log("output is " +  input);
         return input;
     }
     
     function insert_image() {
         var image_url = prompt("Enter image url");
         if (image_url != null) {
             document.getElementById("messageinput").value = document.getElementById("messageinput").value + "{image}" + image_url;
         }
         document.getElementById("messageinput").focus();
     }
     
     function insert_link() {
         var link_url = prompt("Enter link url");
         if (link_url != null) {
             document.getElementById("messageinput").value = document.getElementById("messageinput").value + "{link}" + link_url;
         }
         document.getElementById("messageinput").focus();
     }
     
     function throw_dice() {
         ws.send("/dice " + document.getElementById("dicecount").value + " " + document.getElementById("dicesides").value + " " + document.getElementById("dicerepeat").value);
     }

    function display_received_message(message_text) {
        fromuser = message_text.split(">")[0].split(" ")[1];
        message_text = message_text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        message_text = convert_images_and_links(message_text);
        document.getElementById('messages').innerHTML = "<p id=\"message" + last_message_id +"\" style=\"color: " + userColors[fromuser] +"\">" + message_text + "</p>" + document.getElementById('messages').innerHTML;
        last_message_id++;
        if (last_message_id > number_of_old_messages) { // do not display old messages
            remove_number = last_message_id - number_of_old_messages;
            document.getElementById('messages').removeChild(document.getElementById("message" + remove_number))
        }
    }

    function display_current_RTC_message(message_text) {
        from = message_text.split(" ")[0];
        message_text = message_text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        message_text = convert_images_and_links(message_text);
        if (ignoreList.indexOf(from) ==  -1 ) {
            document.getElementById('RTCmessage').innerHTML = message_text;
        }
    }

    function sendOffers(users) {
        // for each in userlist start new RTC connection and send request to initialize it
        console.log(username + " starting sendoffers " + users);
        
        if (users === undefined || users.length < 1) {
          console.log(username + " ending sendoffers");
        }
        else {
          user = users[users.length-1];
          users.splice(users.length-1,1);
          CurrentPeerConnections[user] = new RTCPeerConnection(MyRTCConfiguration);
          console.log(username + " created RTC object for '" + user + "' in offer");
          CurrentPeerConnections[user].onicecandidate = function (evt) {
               if (evt.candidate != null) {
                   ws.send(user + ": " + JSON.stringify(evt.candidate).replace(/\\r\\n/g,"zalomeni"));
                   console.log(username + " is sending ice candidate from offer to '" + user + "'");
               }
          }
          CurrentRTCDataChannels[user] = CurrentPeerConnections[user].createDataChannel("livechat");
          console.log(username + " creating datachannel for '" + user + "'");
          
          CurrentRTCDataChannels[user].onopen = function (event) {
              console.log(username + " data channel message to " + user + " opened");
          };
          CurrentRTCDataChannels[user].onerror = function(error) {
              console.log(username + " data channel to " + user + " error: " + error);
          };
          CurrentRTCDataChannels[user].onclose = function (event) {
              console.log(username + " data channel message to " + user + " closed: " + event);
          };
          CurrentRTCDataChannels[user].onmessage = function (event) {
              display_current_RTC_message(user + " is writing: " + event.data);
          };
          
          CurrentPeerConnections[user].createOffer(function(offer) {
              CurrentPeerConnections[user].setLocalDescription(new RTCSessionDescription(offer), function() {
                    // need to replace line break, because server will remove \
                    ws.send(user + ": " + JSON.stringify(offer).replace(/\\r\\n/g,"zalomeni"));
                    console.log(username + " is sending offer to RTC with " + user);
                    sendOffers(users);
                    },function(err){console.log(err)});
                },function(err){console.log(err)});
        }
    }
    
    function sendAnswerToOffer(inputMessage) {
        from = inputMessage.substring(inputMessage.indexOf("]")+1, inputMessage.indexOf(">")).trim();
        offer = JSON.parse(inputMessage.substring(inputMessage.indexOf(":",10)+2).replace(/zalomeni/g,"\\r\\n"));
        //console.log("offer: " + offer);
        CurrentPeerConnections[from] = new RTCPeerConnection(MyRTCConfiguration);
        console.log(username + " created RTC object for '" + from + "' in answer");

        CurrentPeerConnections[from].ondatachannel = function(event) {
            console.log(username + " ondatachannel for '" + from + "'");
            CurrentRTCDataChannels[from] = event.channel;
            CurrentRTCDataChannels[from].onopen = function (event) {
              console.log(username + " data channel message to " + from + " opened");
            };
            CurrentRTCDataChannels[from].onerror = function(error) {
              console.log(username + " data channel to " + from + " error: " + error);
            };
            CurrentRTCDataChannels[from].onclose = function(event) {
              console.log(username + " data channel to " + from + " closed: " + event);
            };
            CurrentRTCDataChannels[from].onmessage = function (event) {
              console.log("RTC message from " + from + ": " + event.data);
              display_current_RTC_message(from + " is writing: " + event.data);
            };
        };

        CurrentPeerConnections[from].onicecandidate = function (evt) {
            if (evt.candidate != null) {
                console.log(username + " is sending ice candidate from answer for " + from);
                //console.log("evt.candidate " + JSON.stringify(evt.candidate));
                ws.send(from + ": " + JSON.stringify(evt.candidate).replace(/\\r\\n/g,"zalomeni"));
            }
        }
          
        CurrentPeerConnections[from].setRemoteDescription(new RTCSessionDescription(offer), function() {
            CurrentPeerConnections[from].createAnswer(function(answer) {
                CurrentPeerConnections[from].setLocalDescription(new RTCSessionDescription(answer), function() {
                    // send the answer to a server to be forwarded back to the caller (you)
                    console.log(username + " is sending answer to offer to RTC with " + from);
                    //console.log("answer: " + JSON.stringify(answer));
                    ws.send(from + ": " + JSON.stringify(answer).replace(/\\r\\n/g,"zalomeni"));
                }, function(err){console.log(err)});
            }, function(err){console.log(err)});
        }, function(err){console.log(err)});
    }
    
    function receivedIceCandidate(inputMessage) {
        from = inputMessage.substring(inputMessage.indexOf("]")+1, inputMessage.indexOf(">")).trim();
        console.log(username + " received ice candidate from '" + from + "'");
        signal = JSON.parse(inputMessage.substring(inputMessage.indexOf(":",10)+2).replace(/zalomeni/g,"\\r\\n"));
        CurrentPeerConnections[from].addIceCandidate(new RTCIceCandidate(signal));
    }
    
    function receivedAnswerToOffer(inputMessage) {
        from = inputMessage.substring(inputMessage.indexOf("]")+1, inputMessage.indexOf(">")).trim();
        console.log(username + " received answer to offer from '" + from + "'");
        signal = JSON.parse(inputMessage.substring(inputMessage.indexOf(":",10)+2).replace(/zalomeni/g,"\\r\\n"));
        CurrentPeerConnections[from].setRemoteDescription(new RTCSessionDescription(signal));
    }
    
    function sendToRTCPeers() {
        message = document.getElementById("messageinput").value;
        if (message.length > 0 && message[0] != "/") {
            words = message.split(" ");
            whisperto = words[0].slice(0,-1);
            if (getUserList().indexOf(whisperto) != -1) {
                document.getElementById("sendButton").value = "Whisper";
                if (CurrentRTCDataChannels[whisperto].readyState == "open") {
                    console.log(username + " is sending RTC whisper message '" + message + "' to user " + whisperto);
                    CurrentRTCDataChannels[whisperto].send(message);
                }
                else {
                    console.log(username + " is not sending RTC whisper message '" + message + "' to user " + user + " channel is not open");
                }
            }
            else {
                document.getElementById("sendButton").value = "Send";
                for (user in CurrentRTCDataChannels) {
                    if (CurrentRTCDataChannels[user].readyState == "open") {
                        console.log(username + " is sending RTC message '" + message + "' to user " + user);
                        CurrentRTCDataChannels[user].send(message);
                    }
                    else {
                        console.log(username + " is not sending RTC message '" + message + "' to user " + user + " channel is not open");
                    }
                }
            }
        }
    }
    </script>
  </body>
</html>
