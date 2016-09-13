<?php
$config=parse_ini_file("/home/chat/conf/websocket_chat_server.ini", true);
session_save_path($config["base_dir"]."/sessions");
session_start();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Create new room</title>
    <link rel="stylesheet" type="text/css" href="theme.css">
  </head>
  <body>
  <h1>Create new room</h1>
  <menu><li><a href="room_list.php">List</a><li><a href="room_list_historical.php">History</a><li><a href="userconfig.php">Config</a></menu>
  <form action="index.php" method="GET">
  <table>
    <tr><td><label for="room">Room name</label></td><td><input type="text" name="room"></td></tr>
    <tr><td><label></label></td><td><input type="submit"></td></tr>
  </table>
  </form>
  </body>
</html>

