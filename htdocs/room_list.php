<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Room list</title>
    <style>
html, body { height: 100%; margin: 0; padding-left: 0.5em; padding-top: 0.5em; background: #faebd7; color: black;}
    </style>
  </head>
  <body>
  <h1>Room list</h1>
    <table>
      <tr><th>name</th><th>description</th><th>users</th></tr>
<?php
$config=parse_ini_file("/home/chat/conf/websocket_chat_server.ini", true);
$users = scandir($config["global"]["BASE_DIR"]."/users");
sort($users);
$old = "";
$rooms = array();
foreach ($users as $value) {
    $roomname = explode("_",$value)[0];
    if ($roomname != "." and $roomname != "..") {
        $username = explode("_",$value)[1];
        if ($roomname != $old) {
            $old = $roomname;
            $rooms[$roomname] = array();
        }
        array_push($rooms[$roomname], $username);
    }
}

foreach ($config["room descriptions"] as $roomname => $description ) {
   if ( !isset($rooms[$roomname])) {
       $rooms[$roomname] = array();
   }
}

foreach ($rooms as $name => $usernames) {
    echo "<tr><td><a title=\"Enter\" href=\"index.html?room=".$name."\">".$name."</a></td><td>".$config["room descriptions"][$name]."</td><td>".implode(", ",$usernames)."</td></tr>\n";
}
?>
    </table>
  </body>
</html>

