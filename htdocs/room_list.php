<?php
require_once("header.php");
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Room list</title>
    <link rel="stylesheet" type="text/css" href="theme.css">
  </head>
  <body>
  <h1>Room list</h1>
<?php
include("menu.php");
?>
    <table>
      <tr><th>name</th><th>description</th><th>users</th></tr>
<?php
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
    echo "<tr><td><a title=\"Enter\" href=\"index.php?room=".$name."\">".$name."</a></td><td>".$config["room descriptions"][$name]."</td><td>".implode(", ",$usernames)."</td></tr>\n";
}
?>
    </table>

  </body>
</html>

