<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Room history</title>
    <link rel="stylesheet" type="text/css" href="theme.css">
  </head>
  <body>
  <h1>Room history</h1>
<?php
$config=parse_ini_file("conf/websocket_chat_server.ini", true);
$roomsdir = scandir($config["global"]["BASE_DIR"]."/rooms");
foreach ($roomsdir as $value) {
    if (is_file($config["global"]["BASE_DIR"]."/rooms/".$value)) {	
        echo "<a href=\"room_history.php?file=".$value."\">".$value."</a><br>"; 
    }
}
?>

  <p><a href="room_list.php">Open rooms</a></p>
  </body>
</html>

