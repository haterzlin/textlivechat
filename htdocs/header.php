<?php
$config=parse_ini_file("/etc/opt/websocketd_with_webrtc_chat.ini", true);
session_save_path($config["global"]["BASE_DIR"]."/sessions");
session_start();
?>
