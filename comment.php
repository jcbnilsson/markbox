<?php session_start();
include "core.php";
include "config.php";

// fields
$Date = "";
$Endpoint = "";
$File = "";
$id = -1;
$retid = -1;

$Redirect = "";
$AuthorizedCreation = 0;

if (isset($_REQUEST['redir'])) {
    $Redirect = htmlspecialchars($_REQUEST['redir']);
}

$Database = createTables($sqlDB);
$DatabaseQuery = $Database->query('SELECT * FROM users');

if (!isset($_SESSION['username']) || !isset($_SESSION['password']) || !isset($_SESSION['type'])) {
    header('Location: login.php?redir=admin');
    die();
}

$DatabaseQuery = $Database->query('SELECT * FROM users');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['username'] == htmlspecialchars($_SESSION['username']) && htmlspecialchars($_SESSION['username']) != "" && $line['password'] == htmlspecialchars($_SESSION['password']) && $line['usertype'] == htmlspecialchars($_SESSION['type'])) {
        $AuthorizedCreation = 1;
        break;
    }
}

$Username = htmlspecialchars($_SESSION['username']);
$userType = htmlspecialchars($_SESSION['type']);

// not authorized
if ($AuthorizedCreation != 1) {
    header('Location: /');
    die();
}

// date must be specified
if (isset($_REQUEST['date']) && (htmlspecialchars($_REQUEST['date']) != "")) {
    $Date = htmlspecialchars($_REQUEST['date']);
} else {
    $Date = date($dateFormat);
}

// body must be specified
if (isset($_REQUEST['body']) && htmlspecialchars($_REQUEST['body']) != "") {
    $Body = htmlspecialchars($_REQUEST['body']);
} else {
    if ($Redirect == "admin") {
        header("Location: admin.php?e=body");
    } else if ($Redirect == "edit") {
        header("Location: edit.php?e=body&action=write");
    } else {
        header("Location: /");
    }

    die();
}

// id must be specified
if (isset($_REQUEST['id']) && htmlspecialchars($_REQUEST['id']) != "") {
    $id = htmlspecialchars($_REQUEST['id']);
} else {
    if ($Redirect == "admin") {
        header("Location: admin.php?e=id");
    } else if ($Redirect == "edit") {
        header("Location: edit.php?e=id&action=write");
    } else {
        header("Location: /");
    }

    die();
}

if (isset($_REQUEST['retid']) && htmlspecialchars($_REQUEST['retid']) != "") {
    $retid = htmlspecialchars($_REQUEST['retid']);
} else {
    $retid = -1;
}

// check to make sure it fits in the size boundries
if (mb_strlen($Body, "UTF-8") > $maxCommentSize) {
    header("Location: /?id=$retid");
    die();
}

// check if an endpoint by the same name already exists
$idExists = 0;
$DatabaseQuery = $Database->query('SELECT * FROM pages');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['id'] == $id) {
        $idExists = 1;
    }
}

if ($idExists == 0) {
    if ($Redirect == "admin") {
        header("Location: admin.php?e=id");
    } else if ($Redirect == "edit") {
        header("Location: edit.php?e=id&action=write");
    } else {
        header("Location: /?id=$retid");
    }

    die();
}

$DatabaseQuery = $Database->query('SELECT * FROM comments');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['data'] == $Body) {
        if ($Redirect == "admin") {
            header("Location: admin.php?e=dup");
        } else if ($Redirect == "edit") {
            header("Location: edit.php?e=dup&action=write");
        } else {
            header("Location: /?id=$retid");
        }

        die();
    }
}

$Database->exec("INSERT INTO comments(date, data, username, usertype, page) VALUES('$Date', '$Body', '$Username', '$userType', '$id')");

if ($Redirect == "admin") {
    header("Location: admin.php?action=users");
} else if ($Redirect == "edit") {
    header("Location: edit.php?action=write");
} else {
    header("Location: /?id=$retid");
}

die();

?>
