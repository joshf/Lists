<?php

//Lists, Copyright Josh Fradley (http://github.com/joshf/Lists)

if (!file_exists("config.php")) {
    header("Location: installer");
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

if (isset($_GET["list"])) {
	$list = $_GET["list"];	
} else {
	die("Error: No list passed!");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lists</title>
<link rel="apple-touch-icon" href="assets/icon.png">
<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 30px;
    padding-bottom: 30px;
}
/* Fix weird notification appearance */
a.close.pull-right {
    padding-left: 10px;
}
</style>
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="navbar navbar-default navbar-fixed-top" role="navigation">
<div class="container">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
<span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
<a class="navbar-brand" href="#">Lists</a>
</div>
<div class="navbar-collapse collapse">
<ul class="nav navbar-nav">
<li class="active"><a href="index.php">Home</a></li>
</ul>
<ul class="nav navbar-nav navbar-right">
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $resultgetusersettings["user"]; ?> <b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="settings.php">Settings</a></li>
<li><a href="logout.php">Logout</a></li>
</ul>
</li>
</ul>
</div>
</div>
</div>
<div class="container">
<div class="page-header">
<h1>View List</h1>
</div>
<div class="notifications top-right"></div>
<ul class="list-group">
<?php

$getitems = mysql_query("SELECT * FROM `Data` WHERE list = $list");

if (mysql_num_rows($getitems) != 0) {
    while($row = mysql_fetch_assoc($getitems)) {
    	echo "<li class=\"list-group-item\"><span class=\"delete glyphicon glyphicon-remove pull-right\" data-id=\"" . $row["id"] . "\"></span>" . $row["item"] . "</li>";
    }
} else {
    echo "<li class=\"list-group-item\">No items to show</li>";
}

?>      
</ul>
<button type="button" id="add" class="btn btn-default">Add Item</button>
</div>
<script src="assets/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/bootbox.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    /* Add */
    $("#add").click(function() {
        bootbox.prompt({
            title: "Add Item",
            callback: function(item) {
                if (item != "") {
                    $.ajax({
                        type: "POST",
                        url: "worker.php",
                        data: "action=add&list=<?php echo $list; ?>&item="+ item +"",
                        error: function() {
                            bootbox.alert("Ajax query failed!");
                        },
                        success: function() {
                            window.location.reload();
                        }
                    });
                }
            }
        });
    });
    /* End */
    /* Delete */
    $("li").on("click", ".delete", function() {
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=delete&id="+ id +"",
            error: function() {
                bootbox.alert("Ajax query failed!");
            },
            success: function() {
                window.location.reload();
            }
        });
    });
    /* End */
});
</script>
</body>
</html>