<?php

//Lists, Copyright Josh Fradley (http://github.com/joshf/Lists)

if (!file_exists("config.php")) {
    die("Error: Config file not found!");
}

require_once("config.php");

session_start();
if (!isset($_SESSION["lists_user"])) {
    header("Location: login.php");
    exit;
}

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

$getusersettings = mysqli_query($con, "SELECT `user` FROM `users` WHERE `id` = \"" . $_SESSION["lists_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

if (isset($_GET["listid"])) {
	$listid = mysqli_real_escape_string($con, $_GET["listid"]);	
} else {
	die("Error: No list passed!");
}

$listcheck = mysqli_query($con, "SELECT `id`, `name` FROM `Lists` WHERE `id` = $listid");
if ($listcheck === FALSE || mysqli_num_rows($listcheck) == "0") {
    die("Error: List does not exist!");   
}
$resultlistcheck = mysqli_fetch_assoc($listcheck);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="assets/favicon.ico">
<title>Lists &raquo; View List</title>
<link rel="apple-touch-icon" href="assets/icon.png">
<link rel="stylesheet" href="assets/bower_components/bootstrap/dist/css/bootstrap.min.css" type="text/css" media="screen">
<link rel="stylesheet" href="assets/css/lists.css" type="text/css" media="screen">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container">
<div class="pull-right"><a href="settings.php"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></a> <a href="logout.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span></a></div>
<h1><?php echo $resultlistcheck["name"]; ?></h1>
<ol class="breadcrumb">
<li><a href="index.php">Lists</a></li>
<li class="active"><?php echo $resultlistcheck["name"]; ?></li>
</ol>
<ul class="list-group">
<?php

$getitems = mysqli_query($con, "SELECT * FROM `data` WHERE `list` = \"$listid\" ORDER BY `id` AND `complete`");

if (mysqli_num_rows($getitems) != 0) {
    while($row = mysqli_fetch_assoc($getitems)) {
        echo "<li class=\"list-group-item\">";
        if ($row["complete"] == "0") {
           echo $row["item"]; 
        } else {
            echo "<b><s>" . $row["item"] . "</s></b>";
        }
        echo "<div class=\"pull-right\">";
        if ($row["complete"] == "0") {
            echo "<span class=\"complete glyphicon glyphicon-ok\" data-id=\"" . $row["id"] . "\"></span>";
        } else {
            echo "<span class=\"restore glyphicon glyphicon-repeat\" data-id=\"" . $row["id"] . "\"></span>";
        }
        echo "</div></li>";
        $count++;
    }
} else {
    echo "<li class=\"list-group-item\">No items to show</li>";
}

mysqli_close($con);

?>      
</ul>
<form id="additemform" method="post" autocomplete="off">
<div class="form-group">
<label for="item">Add Item</label>
<input type="text" class="form-control" id="item" name="item" placeholder="Type a item..." required>
</div>
<button type="submit" class="btn btn-default">Add Item</button>
</form>
</div>
<script src="assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/remarkable-bootstrap-notify/dist/bootstrap-notify.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">  
$(document).ready(function () {
    $("#additemform").submit(function() {
        var item = $("#item").val();
        if (item != null && item != "") {
            $.ajax({
                type: "POST",
                url: "worker.php",
                data: "action=add&id=<?php echo $listid; ?>&item="+ item +"",
                error: function() {
                    $.notify({
                        message: "Ajax query failed!",
                        icon: "glyphicon glyphicon-warning-sign",
                    },{
                        type: "danger",
                        allow_dismiss: true
                    });
                },
                success: function() {
                    $.notify({
                        message: "Item added!",
                        icon: "glyphicon glyphicon-ok",
                    },{
                        type: "success",
                        allow_dismiss: true
                    });
                    setTimeout(function() {
                    	window.location.reload();
                    }, 500);
                }
            });
            return false;
        }
    });
    $("li").on("click", ".restore", function(event) {
        var id = $(this).data("id");
        if (event.altKey) {
            $.ajax({
                type: "POST",
                url: "worker.php",
                data: "action=delete&id="+ id +"",
                error: function() {
                    $.notify({
                        message: "Ajax query failed!",
                        icon: "glyphicon glyphicon-warning-sign",
                    },{
                        type: "danger",
                        allow_dismiss: true
                    });
                },
                success: function() {
                    $.notify({
                        message: "Item deleted!",
                        icon: "glyphicon glyphicon-ok",
                    },{
                        type: "success",
                        allow_dismiss: true
                    });
                    setTimeout(function() {
                    	window.location.reload();
                    }, 500);
                }
            });
        } else {
            $.ajax({
                type: "POST",
                url: "worker.php",
                data: "action=restore&id="+ id +"",
                error: function() {
                    $.notify({
                        message: "Ajax query failed!",
                        icon: "glyphicon glyphicon-warning-sign",
                    },{
                        type: "danger",
                        allow_dismiss: true
                    });
                },
                success: function() {
                    $.notify({
                        message: "Item restored!",
                        icon: "glyphicon glyphicon-ok",
                    },{
                        type: "success",
                        allow_dismiss: true
                    });
                    setTimeout(function() {
                    	window.location.reload();
                    }, 500);
                }
            });
        }
    });
    $("li").on("click", ".complete", function() {
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=complete&id="+ id +"",
            error: function() {
                $.notify({
                    message: "Ajax query failed!",
                    icon: "glyphicon glyphicon-warning-sign",
                },{
                    type: "danger",
                    allow_dismiss: true
                });
            },
            success: function() {
                $.notify({
                    message: "Item completed!",
                    icon: "glyphicon glyphicon-ok",
                },{
                    type: "success",
                    allow_dismiss: true
                });
                setTimeout(function() {
                	window.location.reload();
                }, 500);
            }
        });
    });
});
</script>
</body>
</html>