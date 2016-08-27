<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Room history</title>
    <link rel="stylesheet" type="text/css" href="theme.css">
  </head>
  <body>
  <h1>Room history</h1>
    <menu><li><a href="room_create.php">New</a><li><a href="room_list.php">List</a></menu>
<?php
$config=parse_ini_file("conf/websocket_chat_server.ini", true);
$roomsdir = scandir($config["global"]["BASE_DIR"]."/rooms");
foreach ($roomsdir as $value) {
    if (is_file($config["global"]["BASE_DIR"]."/rooms/".$value)) {	
        echo "<a href=\"room_history.php?file=".$value."\">".$value."</a><br>"; 
    }
}
?>

  </body>
</html>

