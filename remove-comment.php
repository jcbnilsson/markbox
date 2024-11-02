<?php session_start();

include "core.php";
include "config.php";

$retid = -1;
$id = -1;

$Redirect = "";
$Authorized = 0;

if (isset($_REQUEST['redir'])) {
    $Redirect = htmlspecialchars($_REQUEST['redir']);
}

if (isset($_REQUEST['id'])) {
    $id = htmlspecialchars($_REQUEST['id']);
} else {
    if ($Redirect == "admin") {
        header("Location: admin.php?e=endpoint&action=comments");
    } else if ($Redirect == "edit") {
        header("Location: edit.php?e=endpoint&action=comments");
    } else {
        header("Location: /");
    }

    die();
}

if (isset($_REQUEST['retid'])) {
    $retid = htmlspecialchars($_REQUEST['retid']);
} else {
    $retid = -1;
}

$Database = createTables($sqlDB);
$DatabaseQuery = $Database->query('SELECT * FROM users');

if (!isset($_SESSION['username']) || !isset($_SESSION['password']) || !isset($_SESSION['type'])) {
    header('Location: login.php?redir=admin');
    die();
}

$DatabaseQuery = $Database->query('SELECT * FROM users');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['username'] == htmlspecialchars($_SESSION['username']) && htmlspecialchars($_SESSION['username']) != "" && $line['password'] == htmlspecialchars($_SESSION['password'])) {
        if ($line['usertype'] == 2) {
            $Authorized = 1;
        } else {
            $CommentDatabaseQuery = $Database->query('SELECT * FROM comments');

            while ($cline = $CommentDatabaseQuery->fetchArray()) {
                if ($cline['id'] == $id && $cline['username'] == htmlspecialchars($_SESSION['username'])) {
                    $Authorized = 1;
                }
            }

            break;
        }
    }
}

$Username = htmlspecialchars($_SESSION['username']);

// not authorized
if ($Authorized != 1) {
    header("Location: /?id=$retid");
    die();
}

$Database->exec("DELETE FROM comments WHERE id='$id'");

if ($Redirect == "admin") {
    header("Location: admin.php?action=comments");
} else if ($Redirect == "edit") {
    header("Location: edit.php?action=comments");
} else {
    header("Location: /?id=$retid");
}

die();

?>
