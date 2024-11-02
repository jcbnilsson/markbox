<?php session_start();

include "config.php";
include "core.php";

if (!isset($_SESSION['username']) ||  !isset($_SESSION['password']) || !isset($_SESSION['type'])) {
    header('Location: login.php?redir=admin');
    die();
} else if (htmlspecialchars($_SESSION['type']) != 2) { // not allowed
    header('Location: /');
    die();
}

$AdminIsPrimary = 0;
$UserIsPrimary = 0;
$AuthorizedRemoval = 0;
$Removed = 0;
$Redirect = "";
$id = 0;
$type = 0;

if (isset($_REQUEST['id'])) {
    $id = htmlspecialchars($_REQUEST['id']);
} else {
    print "No ID specified.";
    die();
}

if (isset($_REQUEST['type'])) {
    $type = htmlspecialchars($_REQUEST['type']);
} else {
    print "No type specified, is not safe to delete.";
    die();
}

if (isset($_REQUEST['redir'])) {
    $Redirect = htmlspecialchars($_REQUEST['redir']);
}

$Database = createTables($sqlDB);
$DatabaseQuery = $Database->query('SELECT * FROM users');

while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['usertype'] == 2 && $line['username'] == htmlspecialchars($_SESSION['username']) && htmlspecialchars($_SESSION['username']) != "" && $line['password'] == htmlspecialchars($_SESSION['password']) && htmlspecialchars($_SESSION['password']) != "") {
        $AuthorizedRemoval = 1;
        $AdminIsPrimary = $line['primaryadmin'];
        break;
    }
}

// not authorized
if ($AuthorizedRemoval != 1) {
    header('Location: /');
    die();
}

$DatabaseQuery = $Database->query('SELECT * FROM users');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['id'] == $id && $line['id'] != "" && $id != "" && $Removed != 1 && $line['primaryadmin'] != 1) {
        if ($AuthorizedRemoval == 1 && (($AdminIsPrimary == 1 && $line['id'] == 2) || $line['id'] != 2)) {
            $Database->exec("DELETE FROM users WHERE id='$id'");
            $Removed = 1;
        } else {
            print "You aren't authorized to perform this action.";
            die();
        }

        break;
    }
}

if ($Redirect == "admin") {
    header("Location: admin.php?action=users");
} else {
    header("Location: /");
}

?>
