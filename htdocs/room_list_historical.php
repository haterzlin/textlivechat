<?php
require_once("header.php");
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
<?php
include("menu.php");
$roomsdir = scandir($config["global"]["BASE_DIR"]."/rooms");
usort($roomsdir, function($a, $b) {
    global $config;
    return filemtime($config["global"]["BASE_DIR"]."/rooms/".$a) < filemtime($config["global"]["BASE_DIR"]."/rooms/".$b);
});
echo "<table>";
echo "<tr><th>Name</th><th>Size (bytes)</th><th>Last modify</th></tr>\n";
foreach ($roomsdir as $value) {
    if (is_file($config["global"]["BASE_DIR"]."/rooms/".$value))  
        if (filesize($config["global"]["BASE_DIR"]."/rooms/".$value) > 0)
            echo "<tr><td><a href=\"room_history.php?file=".$value."\">".$value."</a></td><td>".filesize($config["global"]["BASE_DIR"]."/rooms/".$value)."</td><td>".date("Y-m-d H:s", filemtime($config["global"]["BASE_DIR"]."/rooms/".$value))."</td></tr>\n";
}
echo "</table>";
?>

  </body>
</html>

