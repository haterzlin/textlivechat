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
                        result2 = result2 + '<li style="color: ' + currentusercolor + ' " > <a title="Don\'t ignore ' + currentuser + '" id="ignore' + currentuser + 'link" href="#" onclick="dontIgnoreUser(' + currentuser + ');"><img title="Don\'t ignore user" src="icos/Righthand.svg" height="25px"> <span>' + currentuser + '</span>\n';
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
                 url = input.substring(startindex+6, secondindex);
                 url1 = url.substring(url.indexOf("//") + 2);
                 inputdomain = url1.substring(0, url1.indexOf("/"));
                 if (document.domain == inputdomain) {
                     secondpart = "<a target='_blank' href='" + url + "'>link</a>";
                 }
                 else {
                     secondpart = "<a class=\"external_link\" title=\"external link, take care\" target='_blank' href='" + url + "'>link</a>";
                 }
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
                 url = input.substring(startindex+7, secondindex);
                 url1 = url.substring(url.indexOf("//") + 2);
                 inputdomain = url1.substring(0, url1.indexOf("/"));
                 if (document.domain == inputdomain) {
                     secondpart = "<img src='" + url + "'>";
                 } 
                 else {
                     secondpart = "<a class=\"external_image\" title=\"external image, take care\" target='_blank' href='" + url + "'>external image</a>";
                 }
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
        notify(message_text);
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
          CurrentRTCDataChannels[user].foruser = user;
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
              display_current_RTC_message(this.foruser + " is writing: " + event.data);
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
            CurrentRTCDataChannels[from].foruser = from;
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
              //console.log(event.toString());
              console.log("RTC message from " + from + ": " + event.data);
              display_current_RTC_message(this.foruser + " is writing: " + event.data);
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
    
  function sendNotification(message) {
    if (document.hidden) {
      var n = new Notification("New chat message", { body: message.substring(13,53) + "..." });
      setTimeout(n.close.bind(n), 5000);
    }
  }
 
  function notify(message) {
    if (Notification.permission == "granted") {
            sendNotification(message);
    }
    else {
        if (Notification.permission != "denied") {
            Notification.requestPermission(function (permission) {
                if (permission === "granted") {
                    sendNotification(message);
                }
            });
           
       }
    }
  }
