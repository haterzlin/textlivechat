<?php
$config["menu"]["Create"] = "room_create.php";
$config["menu"]["List"] = "room_list.php";
$config["menu"]["History"] = "room_list_historical.php";
$config["menu"]["Config"] = "userconfig.php";

echo "<menu>";
foreach ($config["menu"] as $key => $value)
   if ("/".$value == $_SERVER["SCRIPT_NAME"])
       echo "<li>".$key;
   else
       echo "<li><a href=\"".$value."\">".$key."</a>"; 
echo "</menu>";
?>
