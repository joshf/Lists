<?php

//Lists, Copyright Josh Fradley (http://github.com/joshf/Lists)

require_once("assets/version.php");

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="assets/favicon.ico">
<title>Lists</title>
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
<h1>Lists</h1>
<ol class="breadcrumb">
<li><a href="index.php">Lists</a></li>
<li class="active">Home</li>
</ol>
<ul class="list-group">
<?php

$getlists = mysqli_query($con, "SELECT * FROM `lists` ORDER BY `id`");

if (mysqli_num_rows($getlists) != 0) {
    while($row = mysqli_fetch_assoc($getlists)) {
        echo "<li class=\"list-group-item\"><span class=\"list\" data-id=\"" . $row["id"] . "\">" . $row["name"] . "</span><div class=\"pull-right\"><span class=\"delete glyphicon glyphicon-remove\" data-id=\"" . $row["id"] . "\"></span></div></li>";
    }
} else {
    echo "<li class=\"list-group-item\">No lists to show</li>";
}

mysqli_close($con);

?>      
</ul>
<form id="addform" method="post" autocomplete="off">
<div class="form-group">
<label for="list">New</label>
<input type="text" class="form-control" id="list" name="list" placeholder="Type a new list..." required autofocus>
</div>
<button type="submit" class="btn btn-default">Add</button>
</form>
<span class="pull-right text-muted"><small>Version <?php echo $version; ?></small></span>
</div>
<script src="assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/js-cookie/src/js.cookie.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/remarkable-bootstrap-notify/dist/bootstrap-notify.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootbox.js/bootbox.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">  
$(document).ready(function () {
    var lists_version = "<?php echo $version; ?>";
    if (!Cookies.get("lists_didcheckforupdates")) {
        $.getJSON("https://api.github.com/repos/joshf/Lists/releases").done(function(resp) {
            var data = resp[0];
            var lists_remote_version = data.tag_name;
            var url = data.zipball_url;
            if (lists_version < lists_remote_version) {
                bootbox.dialog({
                    message: "Lists " + lists_remote_version + " is available. For more information about this update click <a href=\""+ data.html_url + "\" target=\"_blank\">here</a>. Do you wish to download the update? If you click \"Not Now\" you will be not reminded for another 7 days.",
                    title: "Update Available",
                    buttons: {
                        cancel: {
                            label: "Not Now",
                            callback: function() {
                                Cookies.set("lists_didcheckforupdates", "1", { expires: 7 });
                            }
                        },
                        main: {
                            label: "Download Update",
                            className: "btn-primary",
                            callback: function() {
                                window.location.href = data.zipball_url;
                            }
                        }
                    }
                });
            }
        });
    }
    $("li").on("click", ".list", function() {
        var id = $(this).data("id");
        window.location.href = "view.php?listid="+id;
    });
    $("#list").focus();
    $("#addform").submit(function() {
        var list = $("#list").val();
        if (list != null && list != "") {
            $.ajax({
                type: "POST",
                url: "worker.php",
                data: "action=addlist&name="+ list +"",
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
                        message: "List added!",
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
    $("li").on("click", ".delete", function() {
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=deletelist&id="+ id +"",
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
                    message: "List deleted!",
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