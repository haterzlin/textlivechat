<?php
require_once("header.php");
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
<?php
include("menu.php");
?>
  <form action="index.php" method="GET">
  <table>
    <tr><td><label for="room">Room name</label></td><td><input type="text" name="room"></td></tr>
    <tr><td><label></label></td><td><input type="submit"></td></tr>
  </table>
  </form>
  </body>
</html>

