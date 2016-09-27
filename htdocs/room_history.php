<?php
require_once("header.php");
?>
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
include("menu.php");
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
           $words = explode(" ", $line);
           if (isset($words[2]) && $words[2][strlen($words[2])-1] == ":")
               echo "<p>".htmlentities($words[0])." ".htmlentities(substr($words[1], 0, -1))." whispers something to ".htmlentities(substr($words[2], 0, -1))."</p>";
           else
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

