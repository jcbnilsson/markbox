<?php session_start();
include "core.php";
include "config.php";

$id = -1;
$Redirect = "";
$AuthorizedCreation = 0;

if (isset($_REQUEST['redir'])) {
    $Redirect = htmlspecialchars($_REQUEST['redir']);
}

if (isset($_REQUEST['file'])) {
    $id = htmlspecialchars($_REQUEST['file']);
} else {
    if ($Redirect == "admin") {
        header("Location: admin.php?e=file");
    } else if ($Redirect == "edit") {
        header("Location: edit.php?e=file&action=attachments");
    } else {
        header("Location: /");
    }

    die();
}

$Database = createTables($sqlDB);
$DatabaseQuery = $Database->query('SELECT * FROM users');

if (!isset($_SESSION['username']) || !isset($_SESSION['password']) || !isset($_SESSION['type'])) {
    header('Location: login.php?redir=admin');
    die();
} else if (htmlspecialchars($_SESSION['type']) != 2) { // not allowed
    header('Location: /');
    die();
}

$DatabaseQuery = $Database->query('SELECT * FROM users');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['username'] == htmlspecialchars($_SESSION['username']) && htmlspecialchars($_SESSION['username']) != "" && $line['password'] == htmlspecialchars($_SESSION['password']) && $line['usertype'] == 2) {
        $AuthorizedCreation = 1;
        break;
    }
}

$Username = htmlspecialchars($_SESSION['username']);

// not authorized
if ($AuthorizedCreation != 1) {
    header('Location: /');
    die();
}

$attachments = array();
if (is_dir($attachmentLocation)) {
    $attachments = scandir($attachmentLocation);
}

foreach ($attachments as $index => $file) {
    if ($file == "." || $file == "..") {
        continue;
    }

    if ($index != $id) {
        continue;
    }

    $file = "$attachmentLocation/$file";

    if (is_file($file)) {
        unlink($file);
    }
}

if ($Redirect == "admin") {
    header("Location: admin.php?action=users");
} else if ($Redirect == "edit") {
    header("Location: edit.php?action=attachments");
} else {
    header("Location: /");
}

die();

?>
