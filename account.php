<?php session_start();

include "config.php";
include "core.php";

$html = "";
$html = printHeader($html, 0);

$Username = "";
$Password = "";
$ID = -1;
$Primary = 0;
$IsCurrentUser = false;
$Error = "";
$Redirect = "account";

// make sure a username and password is specified for authentication
if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
    $Username = htmlspecialchars($_SESSION['username']);
    $Password = htmlspecialchars($_SESSION['password']);
} else {
    print "Username and password must be specified.";
    die();
}

if (isset($_REQUEST['id'])) {
    $ID = htmlspecialchars($_REQUEST['id']);
} else {
    $ID = -1; // use the username and password to determine
}

if (isset($_REQUEST['redir'])) {
    $Redirect = htmlspecialchars($_REQUEST['redir']);
}

$Authorized = 0;

$Database = createTables($sqlDB);
$DatabaseQuery = $Database->query('SELECT * FROM users');

// check permissions
while ($line = $DatabaseQuery->fetchArray()) {
    if ($ID == -1 && $line['username'] == $Username && $Username != "" && $line['password'] != "" && $Password == $line['password']) {
        $ID = $line['id'];
        $SelUsername = $line['username'];
        $IsCurrentUser = true;
        $Authorized = 1;

        break;
    } else if ($line['username'] == $Username && $Username != "" && $line['password'] != "" && $Password == $line['password'] && $line['usertype'] == 2) { // We're logged into an admin account
        $UserDatabaseQuery = $Database->query('SELECT * FROM users');
        $Primary = $line['primaryadmin'];
        $IsCurrentUser = false;

        // no need to display admin tools for our own account
        if ($ID == $line['id']) {
            $IsCurrentUser = true;
        }

        while ($uline = $UserDatabaseQuery->fetchArray()) {
            if ($ID == $uline['id'] && ($Primary && $uline['usertype'] == 2 || $uline['usertype'] != 2)) {
                $SelUsername = $uline['username'];
                $Authorized = 1;
                break;
            }
        }
    }
}

if ($Authorized == 0) {
    die();
}

if (isset($_REQUEST['e'])) {
    $Error = htmlspecialchars($_REQUEST['e']);
}

$html .= "\t\t\t<h1>Account options</h1>\n";
$html .= "\t\t\t\t<p>This is where you can change account options.</p>\n";

if ($allowPasswordChange || $IsCurrentUser) {
    $html .= "\t\t\t\t<h2>Change password</h2>\n";
    $html .= "\t\t\t\t\t<p>If you need to change your password, you can do so here:</p>\n";
    $html .= "\t\t\t\t\t<form method=\"POST\" action=\"change.php\" method=\"post\" class=\"changePass\">\n";

    if ($IsCurrentUser) {
        $html .= "\t\t\t\t\t\t<label for=\"curpass\">Current password</label>\n";
        $html .= "\t\t\t\t\t\t<input type=\"password\" name=\"curpass\" placeholder=\"Current password\">\n";
    }

    $html .= "\t\t\t\t\t\t<br><br>\n";
    $html .= "\t\t\t\t\t\t<label for=\"newpass\">New password</label>\n";
    $html .= "\t\t\t\t\t\t<input type=\"password\" name=\"newpass\" placeholder=\"New password\">\n";
    $html .= "\t\t\t\t\t\t<label for=\"newpassc\">Confirm</label>\n";
    $html .= "\t\t\t\t\t\t<input type=\"password\" name=\"newpassc\" placeholder=\"Confirm\">\n";
    $html .= "\t\t\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"pass\">\n";
    $html .= "\t\t\t\t\t\t<input type=\"hidden\" name=\"id\"\" value=\"$ID\">\n";
    $html .= "\t\t\t\t\t\t<input type=\"hidden\" name=\"redir\" value=\"$Redirect\">\n";
    $html .= "\t\t\t\t\t\t<input type=\"submit\" value=\"Change password\" name=\"change\">\n";
    $html .= "\t\t\t\t\t</form>\n";

    // handle errors
    if ($Error == "pnone") {
        $html .= "\t\t\t\t<p class=\"userError\">No password specified.</p>\n";
    } else if ($Error == "pmismatch") {
        $html .= "\t\t\t\t<p class=\"userError\">Passwords do not match.</p>\n";
    } else if ($Error == "pauth") {
        $html .= "\t\t\t\t<p class=\"userError\">Incorrect password.</p>\n";
    }
}

if ($allowUsernameChange || !$IsCurrentUser) {
    $html .= "\t\t\t\t<h2>Change username</h2>\n";
    $html .= "\t\t\t\t\t<p>If you need to change your username, you can do so here:</p>\n";
    $html .= "\t\t\t\t\t<form method=\"POST\" action=\"change.php\" method=\"post\" class=\"changeUser\">\n";

    if ($IsCurrentUser) {
        $html .= "\t\t\t\t\t\t<label for=\"curusername\">Current username</label>\n";
        $html .= "\t\t\t\t\t\t<input type=\"text\" name=\"curusername\" placeholder=\"Current username\">\n";
    }

    $html .= "\t\t\t\t\t\t<label for=\"newusername\">New username</label>\n";
    $html .= "\t\t\t\t\t\t<input type=\"text\" name=\"newusername\" placeholder=\"New username\">\n";
    $html .= "\t\t\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"username\">\n";
    $html .= "\t\t\t\t\t\t<input type=\"hidden\" name=\"id\"\" value=\"$ID\">\n";
    $html .= "\t\t\t\t\t\t<input type=\"hidden\" name=\"redir\" value=\"$Redirect\">\n";
    $html .= "\t\t\t\t\t\t<input type=\"submit\" value=\"Change username\" name=\"change\">\n";
    $html .= "\t\t\t\t\t</form>\n";

    // handle errors
    if ($Error == "unone") {
        $html .= "\t\t\t\t<p class=\"userError\">No username specified.</p>\n";
    } else if ($Error == "ucurrent") {
        $html .= "\t\t\t\t<p class=\"userError\">You must specify your current username.</p>\n";
    } else if ($Error == "umismatch") {
        $html .= "\t\t\t\t<p class=\"userError\">Usernames do not match.</p>\n";
    } else if ($Error == "uexists") {
        $html .= "\t\t\t\t<p class=\"userError\">A user by that name already exists. Sorry.</p>\n";
    }
}

if (!$IsCurrentUser) {
    $html .= "\t\t\t\t<h2>Administrator: Change type</h2>\n";
    $html .= "\t\t\t\t\t<p>If you need to change the type, you can do so here:</p>\n";

    $html .= "\t\t\t\t\t<form method=\"POST\" action=\"change.php\" method=\"post\" class=\"changeType\">\n";
    $html .= "\t\t\t\t\t\t<label for=\"type\">New type</label>\n";
    $html .= "\t\t\t\t\t\t<select name=\"type\" required>\n";
    if ($Primary == 1) $html .= "\t\t\t\t\t\t\t<option value=\"2\">Administrator</option>\n";
    $html .= "\t\t\t\t\t\t\t<option value=\"1\" selected=\"selected\">User</option>\n";
    $html .= "\t\t\t\t\t\t</select>\n";
    $html .= "\t\t\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"type\">\n";
    $html .= "\t\t\t\t\t\t<input type=\"hidden\" name=\"id\"\" value=\"$ID\">\n";
    $html .= "\t\t\t\t\t\t<input type=\"hidden\" name=\"redir\" value=\"$Redirect\">\n";
    $html .= "\t\t\t\t\t\t<input type=\"submit\" value=\"Change type\" name=\"change\">\n";
    $html .= "\t\t\t\t\t</form>\n";
}

// handle errors
if ($Error == "auth") {
    $html .= "\t\t\t\t<p class=\"userError\">Invalid username or password.</p>\n";
} else if ($Error == "action") {
    $html .= "\t\t\t\t<p class=\"userError\">Invalid action.</p>\n";
}

$html = printFooter($html);
print "$html";

?>
