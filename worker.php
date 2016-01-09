<?php

//Lists, Copyright Josh Fradley (http://github.com/joshf/Lists)

if (!file_exists("config.php")) {
    die("Error: Config file not found!");
}

require_once("config.php");

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

session_start();
if (isset($_POST["api_key"]) || isset($_GET["api_key"])) {
    if (isset($_POST["api_key"])) {
        $api_key = mysqli_real_escape_string($con, $_POST["api_key"]);
    } elseif (isset($_GET["api_key"])) {
        $api_key = mysqli_real_escape_string($con, $_GET["api_key"]);
    }
    if (empty($api_key)) {
        die("Error: No API key passed!");
    }
    $checkkey = mysqli_query($con, "SELECT `id`, `user` FROM `users` WHERE `api_key` = \"$api_key\"");
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

$getusersettings = mysqli_query($con, "SELECT `user` FROM `users` WHERE `id` = \"" . $_SESSION["lists_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

if (isset($_POST["action"])) {
    $action = $_POST["action"];
} elseif (isset($_GET["action"])) {
    $action = $_GET["action"];
} else {
	die("Error: No action passed!");
}

//Check if ID exists
$actions = array("add", "complete", "restore", "delete", "deletelist", "info");
if (in_array($action, $actions)) {
    if (isset($_POST["id"]) || isset($_GET["id"])) {
        if (isset($_POST["action"])) {
            $id = mysqli_real_escape_string($con, $_POST["id"]);
        } elseif (isset($_GET["action"])) {
            $id = mysqli_real_escape_string($con, $_GET["id"]);
        }
        if ($action == "deletelist" || $action == "add" || $action == "info") {
            $checkid = mysqli_query($con, "SELECT `id` FROM `lists` WHERE `id` = $id");
        } else {
            $checkid = mysqli_query($con, "SELECT `id` FROM `data` WHERE `id` = $id");
        }        
        if (mysqli_num_rows($checkid) == 0) {
        	die("Error: ID does not exist!");
        }
    } else {
    	die("Error: ID not set!");
    }
}

//Define variables
if (isset($_POST["item"])) {
    $item = mysqli_real_escape_string($con, $_POST["item"]);
}

if ($action == "add") {
    mysqli_query($con, "INSERT INTO `data` (`list`, `item`, `created`)
    VALUES (\"$id\",\"$item\",CURDATE())");
    
    echo "Info: Item added!";
} elseif ($action == "addlist") {
    $name = strip_tags(mysqli_real_escape_string($con, $_POST["name"]));
    mysqli_query($con, "INSERT INTO `Lists` (`name`)
    VALUES (\"$name\")");
    
    echo "Info: List added!";
} elseif ($action == "complete") {
    mysqli_query($con, "UPDATE `data` SET `complete` = \"1\" WHERE `id` = \"$id\"");
    
    echo "Info: Item marked as completed!";
} elseif ($action == "restore") {
    mysqli_query($con, "UPDATE `data` SET `complete` = \"0\" WHERE `id` = \"$id\"");
    
    echo "Info: Item restored!";
    
} elseif ($action == "delete") {
    mysqli_query($con, "DELETE FROM `data` WHERE `id` = \"$id\"");
    
    echo "Info: Item deleted!";
} elseif ($action == "deletelist") {
    mysqli_query($con, "DELETE FROM `lists` WHERE `id` = \"$id\"");
    mysqli_query($con, "DELETE FROM `data` WHERE `list` = \"$id\"");
    
    echo "Info: List deleted!";
} elseif ($action == "info") {
    
    $getdata = mysqli_query($con, "SELECT `id`, `item`, `created`, `complete` FROM `data` WHERE `list` = \"$id\"");
    
    while($item = mysqli_fetch_assoc($getdata)) {
    
        $data[] = array(
            "id" => $item["id"],
            "item" => $item["item"],
            "created" => $item["created"],
            "complete" => $item["complete"]
        );
    
    }
    echo json_encode(array("data" => $data));
    
} elseif ($action == "generateapikey") {
    $api_key = substr(str_shuffle(MD5(microtime())), 0, 50);
    mysqli_query($con, "UPDATE `users` SET `api_key` = \"$api_key\" WHERE `id` = \"" . $_SESSION["indication_user"] . "\"");
    echo $api_key;
} else {
    die("Error: Action not recognised!");
}

mysqli_close($con);

?>