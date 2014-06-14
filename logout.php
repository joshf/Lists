<?php

//Lists, Copyright Josh Fradley (http://github.com/joshf/Lists)

session_start();

unset($_SESSION["lists_user"]);

if (isset($_COOKIE["lists_user_rememberme"])) {
    setcookie("lists_user_rememberme", "", time()-86400);
}

header("Location: login.php?logged_out=true");

exit;

?>