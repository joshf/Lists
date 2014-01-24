<?php

//Lists, Copyright Josh Fradley (http://github.com/joshf/Lists)

if (!file_exists("config.php")) {
    header("Location: ../installer");
    exit;
}

require_once("config.php");

session_start();
if (!isset($_SESSION["lists_user"])) {
    header("Location: login.php");
    exit; 
}

//Connect to database
@$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die("Error: Could not connect to database (" . mysql_error() . "). Check your database settings are correct.");
}

mysql_select_db(DB_NAME, $con);

$getusersettings = mysql_query("SELECT `user` FROM `Users` WHERE `id` = \"" . $_SESSION["lists_user"] . "\"");
if (mysql_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysql_fetch_assoc($getusersettings);

if (isset($_POST["id"])) {
    $id = mysql_real_escape_string($_POST["id"]);
}

if (isset($_POST["action"])) {
    $action = $_POST["action"];
} else {
	die("Error: No action passed");
}

if ($action == "add") {
    $list = mysql_real_escape_string($_POST["list"]);
    $item = mysql_real_escape_string($_POST["item"]);
    $created = date("d/m/Y");
    mysql_query("INSERT INTO `Data` (`list`, `item`, `created`, `user`)
    VALUES (\"$list\",\"$item\",\"$created\",\"" . $_SESSION["lists_user"] . "\")");
} elseif ($action == "addlist") {
	$name = mysql_real_escape_string($_POST["name"]);
	mysql_query("INSERT INTO `Lists` (`name`)
	VALUES (\"$name\")");
}  elseif ($action == "delete") {
    mysql_query("DELETE FROM `Data` WHERE `id` = \"$id\"");
}  elseif ($action == "deletelist") {
    mysql_query("DELETE FROM `Lists` WHERE `id` = \"$id\"");
}

mysql_close($con);

?>