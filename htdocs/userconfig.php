<?php
$config=parse_ini_file("/app/iwad/conf/websocket_chat_server.ini", true);
session_save_path($config["global"]["BASE_DIR"]."/sessions");
session_start();
$username = "";
$color = "black";
$message = "";
//if (isset($_POST["new_color"]) && is_numeric(substr($_POST["new_color"], 1)))
if (isset($_POST["new_color"]))
  $color = htmlspecialchars($_POST["new_color"]);
if (isset($_POST["new_username"])) 
  $username = htmlspecialchars(escapeshellcmd($_POST["new_username"]));
if ($username != "") {
  $_SESSION["username"] = $username .":".$color;
  $message="Successfully saved username ".$username." and color <span style=\"color:".$color."\">".$color."</span>";
}
if (isset($_SESSION["username"])) {
  $username = explode(":", $_SESSION["username"])[0];
  $color = explode(":", $_SESSION["username"])[1];
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>User config</title>
    <link rel="stylesheet" type="text/css" href="theme.css">
  </head>
  <body>
  <h1>User config</h1>
    <menu><li><a href="room_create.php">New</a><li><a href="room_list.php">List</a><li><a href="room_list_historical.php">History</a></menu>
<?php
if ($message != "")
  echo "<p class=\"INFO\">".$message."</p>";
?>
    <form method="POST" action="userconfig.php">
      <table>
      <tr><td><label for="new_username">Username: </label></td><td> <input type="text" name="new_username" id="new_username" value="<?php echo $username; ?>"></td></tr>
      <tr><td><label for="new_color">Color: </label></td><td> <input type="color" name="new_color" id="new_color" value="<?php echo $color; ?>"></td></tr>
      <tr><td><input type="submit"></td><td></td></tr>
      </table>
    </form>
  </body>
</html>
