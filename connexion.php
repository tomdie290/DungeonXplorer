<?php
/* fileZila
$mysql_user   = "dx05";
$mysql_pass   = "uiK2ieNiez3aigie";
$mysql_db     = "dx05_bd";
$mysql_server = "localhost";
*/
$mysql_user   = "root";
$mysql_pass   = "";
$mysql_db     = "dungeonxplorer";
$mysql_server = "localhost";


$conn = new PDO(
    "mysql:host=$mysql_server;dbname=$mysql_db;charset=utf8",
    $mysql_user,
    $mysql_pass
);


$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
?>
