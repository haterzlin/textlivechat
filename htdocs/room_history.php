<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
echo "    <title>Room history - ".htmlentities($_GET["file"])."</title>";
?>
    <link rel="stylesheet" type="text/css" href="theme.css">
  </head>
  <body>
<?php
echo "  <h1>Room history - ".htmlentities($_GET["file"])."</h1>";
?>
  <menu><li><a href="room_create.php">New</a><li><a href="room_list.php">List</a><li><a href="room_list_historical.php">History</a></menu>
<?php
$config=parse_ini_file("conf/websocket_chat_server.ini", true);
$roomsdir = scandir($config["global"]["BASE_DIR"]."/rooms");

if (in_array($_GET["file"], $roomsdir)) {
  echo "Last modified: ".date ("F d Y H:i:s", filemtime($config["global"]["BASE_DIR"]."/rooms/".$_GET["file"]));
  $lines = file($config["global"]["BASE_DIR"]."/rooms/".$_GET["file"]);
  foreach ($lines as $line) {
    if (strpos($line, "] Userlist: ") === false and
        strpos($line, "\"type\":\"offer\",\"sdp\":\"v=0") == false and
        strpos($line, "\"type\":\"answer\",\"sdp\":\"v=0") == false and
        strpos($line, "{\"candidate\":\"candidate:") == false
       ) {
          echo "<p>".htmlentities($line)."</p>";
    }
  } 
}
else {
  header("Location: room_list_historical.php");
}

?>
  </body>
</html>

