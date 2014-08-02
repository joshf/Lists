<?php

//Lists, Copyright Josh Fradley (http://github.com/joshf/Lists)

require_once("assets/version.php");

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

//Set cookie so we dont constantly check for updates
setcookie("listsupdatecheck", time(), time()+3600*24*7);

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

$getusersettings = mysqli_query($con, "SELECT `user` FROM `Users` WHERE `id` = \"" . $_SESSION["lists_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui">
<title>Lists</title>
<link rel="apple-touch-icon" href="assets/icon.png">
<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 30px;
    padding-bottom: 30px;
}
.delete, .colour {
    cursor: pointer;
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
<a class="navbar-brand" href="index.php">Lists</a>
</div>
<div class="navbar-collapse collapse">
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
<h1><?php echo $resultgetusersettings["user"]; ?>'s Lists</h1>
</div>
<?php

//Update checking
if (!isset($_COOKIE["listsupdatecheck"])) {
    $remoteversion = file_get_contents("https://raw.github.com/joshf/Lists/master/version.txt");
    if (version_compare($version, $remoteversion) < 0) {            
        echo "<div class=\"alert alert-warning\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button><h4 class=\"alert-heading\">Update</h4><p>Lists <a href=\"https://github.com/joshf/Lists/releases/$remoteversion\" class=\"alert-link\" target=\"_blank\">$remoteversion</a> is available. <a href=\"https://github.com/joshf/Lists#updating\" class=\"alert-link\" target=\"_blank\">Click here for instructions on how to update</a>.</p></div>";
    }
} 

?>
<ul class="list-group">
<?php

$getlists = mysqli_query($con, "SELECT * FROM `Lists`");

if (mysqli_num_rows($getlists) != 0) {
    while($row = mysqli_fetch_assoc($getlists)) {
        if ($row["colour"] != "") {
            $colour = $row["colour"];
        } else {
            $colour = "FFFFFF";
        }
        echo "<li class=\"list-group-item\" style=\"background-color: #" . $colour . "\"><a href=\"view.php?list=" . $row["id"] . "\">" . $row["name"] . "</a><div class=\"pull-right\"><span class=\"colour glyphicon glyphicon-adjust\" data-id=\"" . $row["id"] . "\" data-colour=\"" . $colour . "\"></span> <span class=\"delete glyphicon glyphicon-remove\" data-id=\"" . $row["id"] . "\"></span></div></li>";
    }
} else {
    echo "<li class=\"list-group-item\">No lists to show</li>";
}

mysqli_close($con);

?>      
</ul>
<form role="form" id="addform" method="post" autocomplete="off">
<div class="form-group">
<label for="list">Add List</label>
<input type="text" class="form-control" id="list" name="list" placeholder="Type a new list..." required>
</div>
<button type="submit" class="btn btn-default">Add List</button>
</form>
<hr>
<div class="footer">
Lists <?php echo $version; ?> &copy; <a href="http://joshf.co.uk" target="_blank">Josh Fradley</a> <?php echo date("Y"); ?>. Themed by <a href="http://getbootstrap.com" target="_blank">Bootstrap</a>.
</div>
</div>
<script src="assets/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/bootbox.min.js"></script>
<script src="assets/paintit.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    /* Add */
    $("#list").focus();
    $("#addform").submit(function() {
        var list = $("#list").val();
        if (list != null && list != "") {
            $.ajax({
                type: "POST",
                url: "worker.php",
                data: "action=addlist&name="+ list +"",
                error: function() {
                    bootbox.alert("Ajax query failed!");
                },
                success: function() {
                    window.location.reload();
                }
            });
            return false;
        }
    });
    /* End */
    /* List Colour */
    $("li").on("click", ".colour", function() {
        var id = $(this).data("id");
        bootbox.dialog({
            message: "<div class=\"form-group\"><select class=\"form-control\" id=\"colour\" name=\"colour\"><option value=\"FFFFFF\">None</option><option value=\"B3B3B3\">Grey</option><option value=\"d9534f\">Red</option><option value=\"5cb85c\">Green</option><option value=\"5bc0de\">Blue</option><option value=\"f0ad4e\">Yellow</option></select></div>",
            title: "Choose List Colour",
            buttons: {
                main: {
                    label: "Set",
                    className: "btn-primary",
                    callback: function() {
                        var colour = $("#colour").val()
                        $(id).paintit(colour);            
                    }
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
            data: "action=deletelist&id="+ id +"",
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