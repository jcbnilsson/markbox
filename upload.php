<?php session_start();

include "core.php";
include "config.php";

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
} else if (htmlspecialchars($_SESSION['type']) != 2) { // not allowed
    header('Location: /');
    die();
}

$DatabaseQuery = $Database->query('SELECT * FROM users');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['username'] == htmlspecialchars($_SESSION['username']) && htmlspecialchars($_SESSION['username']) != "" && $line['password'] == htmlspecialchars($_SESSION['password']) && htmlspecialchars($line['usertype']) == 2) {
        $AuthorizedCreation = 1;
        break;
    }
}

// not authorized
if ($AuthorizedCreation != 1) {
    header('Location: /');
    die();
}

if (isset($_FILES['file']['tmp_name'])) {
    $Body = htmlspecialchars(file_get_contents($_FILES['file']['tmp_name']));
    $Filename = basename($_FILES["file"]["name"]);

    if (file_exists("/$attachmentLocation/$Filename")) {
        $Filename = hash('sha256', $Filename);
    }

    $File = "$attachmentLocation/$Filename";

    if (!move_uploaded_file(htmlspecialchars($_FILES['file']['tmp_name']), $File)) {
        if ($Redirect == "admin") {
            header("Location: admin.php?e=fail");
        } else if ($Redirect == "edit") {
            header("Location: edit.php?action=attachments&e=fail");
        } else {
            header("Location: /");
        }

        die();
    }
} else {
    if ($Redirect == "admin") {
        header("Location: admin.php?e=file");
    } else if ($Redirect == "edit") {
        header("Location: edit.php?action=attachments&e=file");
    } else {
        header("Location: /");
    }

    die();
}

if ($Redirect == "admin") {
    header("Location: admin.php?action=users");
} else if ($Redirect == "edit") {
    header("Location: edit.php?action=attachments");
} else {
    header("Location: /");
}

?>
