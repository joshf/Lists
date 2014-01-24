<?php

//Lists, Copyright Josh Fradley (http://github.com/joshf/Lists)

session_start();

unset($_SESSION["lists_user"]);

header("Location: login.php?logged_out=true");

exit;

?>