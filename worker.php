<?php

//Lists, Copyright Josh Fradley (http://github.com/joshf/Lists)

if (!file_exists("config.php")) {
    header("Location: installer");
    exit;
}

require_once("config.php");

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

session_start();
if (isset($_POST["api_key"])) {
    $api = mysqli_real_escape_string($con, $_POST["api_key"]);
    if (empty($api)) {
        die("Error: No API key passed!");
    }
    $checkkey = mysqli_query($con, "SELECT `id`, `user` FROM `Users` WHERE `api_key` = \"$api\"");
    $checkkeyresult = mysqli_fetch_assoc($checkkey);
    if (mysqli_num_rows($checkkey) == 0) {
        die("Error: API key is not valid!");
    } else {
        $_SESSION["lists_user"] = $checkkeyresult["id"];
    }
}

if (!isset($_SESSION["lists_user"])) {
    header("Location: login.php");
    exit;
}

$getusersettings = mysqli_query($con, "SELECT `user` FROM `Users` WHERE `id` = \"" . $_SESSION["lists_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

if (isset($_POST["id"])) {
    $id = mysqli_real_escape_string($con, $_POST["id"]);
}

if (isset($_POST["action"])) {
    $action = $_POST["action"];
} else {
	die("Error: No action passed!");
}

if ($action == "add") {
    $list = mysqli_real_escape_string($con, $_POST["list"]);
    $item = strip_tags(mysqli_real_escape_string($con, $_POST["item"]));
    mysqli_query($con, "INSERT INTO `Data` (`list`, `item`, `created`)
    VALUES (\"$list\",\"$item\",CURDATE())");
} elseif ($action == "addlist") {
    $name = strip_tags(mysqli_real_escape_string($con, $_POST["name"]));
    mysqli_query($con, "INSERT INTO `Lists` (`name`)
    VALUES (\"$name\")");
}  elseif ($action == "complete") {
    mysqli_query($con, "DELETE FROM `Data` WHERE `id` = \"$id\"");
}  elseif ($action == "deletelist") {
    mysqli_query($con, "DELETE FROM `Lists` WHERE `id` = \"$id\"");
    mysqli_query($con, "DELETE FROM `Data` WHERE `list` = \"$id\"");
} elseif ($action == "info") {
    $getinfo = mysqli_query($con, "SELECT * FROM `Data` WHERE `id` = \"$id\"");
    $resultgetinfo = mysqli_fetch_assoc($getinfo);
    echo $resultgetinfo["item"];
} elseif ($action == "edit") {
    $item = strip_tags(mysqli_real_escape_string($con, $_POST["item"]));
    mysqli_query($con, "UPDATE `Data` SET `item` = \"$item\" WHERE `id` = \"$id\"");
} elseif ($action == "listcolour") {
    $colour = mysqli_real_escape_string($con, $_POST["colour"]);
    mysqli_query($con, "UPDATE `Lists` SET `colour` = \"$colour\" WHERE `id` = \"$id\"");
} elseif ($action == "generateapikey") {
    $api = substr(str_shuffle(MD5(microtime())), 0, 50);
    mysqli_query($con, "UPDATE `Users` SET `api_key` = \"$api\" WHERE `id` = \"" . $_SESSION["lists_user"] . "\"");
    echo $api;
} else {
    die("Error: Action not recognised!");
}

mysqli_close($con);

?>