<?php
$config=parse_ini_file("/home/chat/conf/websocket_chat_server.ini", true);
session_save_path($config["base_dir"]."/sessions");
session_start();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Room history</title>
    <link rel="stylesheet" type="text/css" href="theme.css">
  </head>
  <body>
  <h1>Room history</h1>
    <menu><li><a href="room_create.php">New</a><li><a href="room_list.php">List</a><li><a href="set_username.php">Username</a></menu>
<?php
$config=parse_ini_file("/app/iwad/conf/websocket_chat_server.ini", true);
$roomsdir = scandir($config["global"]["BASE_DIR"]."/rooms");
echo "<table>";
echo "<tr><th>Name</th><th>Size (bytes)</th></tr>\n";
foreach ($roomsdir as $value) {
    if (is_file($config["global"]["BASE_DIR"]."/rooms/".$value)) {
        echo "<tr><td><a href=\"room_history.php?file=".$value."\">".$value."</a></td><td>".filesize($config["global"]["BASE_DIR"]."/rooms/".$value)."</td></tr>\n";
    }
}
echo "</table>";
?>

  </body>
</html>

