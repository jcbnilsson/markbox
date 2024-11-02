<?php session_start();

include "config.php";
include "core.php";

$Username = "";
$Password = "";
$CurUsername = "";
$CurPassword = "";
$RequestedType = 0;
$Action = "";
$ID = 0;
$Primary = 0;
$UserType = 0;
$IsCurrentUser = false;
$Redirect = "";

if (isset($_REQUEST['redir'])) {
    $Redirect = htmlspecialchars($_REQUEST['redir']);
}

// make sure a username and password is specified for authentication
if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
    $Username = htmlspecialchars($_SESSION['username']);
    $Password = htmlspecialchars($_SESSION['password']);
} else {
    if ($Redirect == "account") {
        header("Location: account.php?id=$ID&e=auth");
        die();
    } else if ($Redirect == "admin") {
        header("Location: admin.php?action=users");
        die();
    } else {
        header("Location: /");
        die();
    }
}

if (isset($_REQUEST['id'])) {
    $ID = htmlspecialchars($_REQUEST['id']);
} else {
    $ID = -1; // use the username and password to determine
}

// action
if (isset($_REQUEST['action'])) {
    $Action = htmlspecialchars($_REQUEST['action']);
} else {
    if ($Redirect == "account") {
        header("Location: account.php?id=$ID&e=action");
        die();
    } else if ($Redirect == "admin") {
        header("Location: admin.php?action=users");
        die();
    } else {
        header("Location: /");
        die();
    }
}

$Authorized = 0;

$Database = createTables($sqlDB);
$DatabaseQuery = $Database->query('SELECT * FROM users');

// check permissions
while ($line = $DatabaseQuery->fetchArray()) {
    if ($ID == -1 && $line['username'] == $Username && $Username != "" && $line['password'] != "" && $Password == $line['password']) {
        $ID = $line['id'];
        $Authorized = 1;
        $IsCurrentUser = true;
        $CurUsername = $line['username'];
        $CurPassword = $line['password'];
        $UserType = $line['usertype'];

        break;
    } else if ($line['username'] == $Username && $Username != "" && $line['password'] != "" && $Password == $line['password']) { // We're logged into an admin account
        $UserDatabaseQuery = $Database->query('SELECT * FROM users');
        $Primary = $line['primaryadmin'];

        if ($ID == $line['id']) {
            $IsCurrentUser = true;
        }

        while ($uline = $UserDatabaseQuery->fetchArray()) {
            if ($ID == $uline['id'] && ($Primary && $uline['usertype'] == 2 || $uline['usertype'] != 2)) {
                $CurUsername = $uline['username'];
                $CurPassword = $uline['password'];
                $UserType = $uline['usertype'];
                $Authorized = 1;
                break;
            }
        }
    }
}

if ($Authorized == 0) {
    if ($Redirect == "account") {
        header("Location: account.php?id=$ID&e=auth");
        die();
    } else if ($Redirect == "admin") {
        header("Location: admin.php?action=users");
        die();
    } else {
        header("Location: /");
        die();
    }
}

// perform the action
if ($Action == "pass" && ($allowPasswordChange || !$IsCurrentUser)) {
    if (!isset($_REQUEST['newpass']) || !isset($_REQUEST['newpassc'])) {
        if ($Redirect == "account") {
            header("Location: account.php?id=$ID&e=pnone");
            die();
        } else if ($Redirect == "admin") {
            header("Location: admin.php?action=users");
            die();
        } else {
            header("Location: /");
            die();
        }
    }

    if (htmlspecialchars($_REQUEST['newpass']) != htmlspecialchars($_REQUEST['newpassc'])) {
        if ($Redirect == "account") {
            header("Location: account.php?id=$ID&e=pmismatch");
            die();
        } else if ($Redirect == "admin") {
            header("Location: admin.php?action=users");
            die();
        } else {
            header("Location: /");
            die();
        }
    }

    $NewPassword = generatePassword(htmlspecialchars($_REQUEST['newpass']));

    if (!password_verify(htmlspecialchars($_REQUEST['curpass']), $CurPassword) && $IsCurrentUser) {
        if ($Redirect == "account") {
            header("Location: account.php?id=$ID&e=pauth");
            die();
        } else if ($Redirect == "admin") {
            header("Location: admin.php?action=users");
            die();
        } else {
            header("Location: /");
            die();
        }
    }

    $Database->exec("UPDATE users SET password='$NewPassword' WHERE id='$ID'");
} else if ($Action == "username" && ($allowUsernameChange || !$IsCurrentUser)) {
    if (!isset($_REQUEST['newusername'])) {
        if ($Redirect == "account") {
            header("Location: account.php?id=$ID&e=unone");
            die();
        } else if ($Redirect == "admin") {
            header("Location: admin.php?action=users");
            die();
        } else {
            header("Location: /");
            die();
        }
    }

    if (!isset($_REQUEST['curusername']) && $IsCurrentUser) {
        if ($Redirect == "account") {
            header("Location: account.php?id=$ID&e=ucurrent");
            die();
        } else if ($Redirect == "admin") {
            header("Location: admin.php?action=users");
            die();
        } else {
            header("Location: /");
            die();
        }
    }

    $NewUsername = htmlspecialchars($_REQUEST['newusername']);

    if ($CurUsername != htmlspecialchars($_REQUEST['curusername']) && $IsCurrentUser) {
        if ($Redirect == "account") {
            header("Location: account.php?id=$ID&e=umismatch");
            die();
        } else if ($Redirect == "admin") {
            header("Location: admin.php?action=users");
            die();
        } else {
            header("Location: /");
            die();
        }
    }

    // make sure no duplicates can exist
    $UserDatabaseQuery = $Database->query('SELECT * FROM users');
    while ($uline = $UserDatabaseQuery->fetchArray()) {
        if ($uline['username'] == $NewUsername) {
            if ($Redirect == "account") {
                header("Location: account.php?id=$ID&e=uexists");
                die();
            } else if ($Redirect == "admin") {
                header("Location: admin.php?action=users");
                die();
            } else {
                header("Location: /");
                die();
            }

            break;
        }
    }

    // change it
    $Database->exec("UPDATE users SET username='$NewUsername' WHERE id='$ID'");
    $Database->exec("UPDATE comments SET username='$NewUsername' WHERE username='$CurUsername'");
} else if ($Action == "type") {
    if (isset($_REQUEST['type']) && !$IsCurrentUser) {
        $UserType = htmlspecialchars($_REQUEST['type']);
    } else {
        header("Location: /");
        die();
    }

    if (($Primary == 1 && $UserType == 2) || $UserType != 2) {
        $Database->exec("UPDATE users SET usertype='$UserType' WHERE id='$ID'");
        $Database->exec("UPDATE comments SET usertype='$UserType' WHERE username='$CurUsername'");
    }
} else {
    header("Location: /");
    die();
}

if ($IsCurrentUser) {
    header('Location: login.php?logout=true');
    die();
} else {
    if ($Redirect == "account") {
        header("Location: account.php?id=$ID");
        die();
    } else if ($Redirect == "admin") {
        header("Location: admin.php?action=users");
        die();
    } else {
        header("Location: /");
        die();
    }
}
?>
