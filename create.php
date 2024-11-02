<?php session_start();

include "core.php";
include "config.php";

// fields
$Date = "";
$Endpoint = "";
$File = "";

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

// date must be specified
if (isset($_REQUEST['date']) && (htmlspecialchars($_REQUEST['date']) != "")) {
    $Date = htmlspecialchars($_REQUEST['date']);
} else {
    $Date = date($dateFormat);
}

// endpoint must be specified
if (isset($_REQUEST['endpoint']) && htmlspecialchars($_REQUEST['endpoint']) != "") {
    $Endpoint = htmlspecialchars($_REQUEST['endpoint']);
} else {
    if ($Redirect == "admin") {
        header("Location: admin.php?e=endpoint");
    } else if ($Redirect == "edit") {
        header("Location: edit.php?e=endpoint&action=write");
    } else {
        header("Location: /");
    }

    die();
}

// check if an endpoint by the same name already exists
$DatabaseQuery = $Database->query('SELECT * FROM pages');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['endpoint'] == "$Endpoint" && $Endpoint != "" && $line['endpoint'] != "") {
        if ($Redirect == "admin") {
            header("Location: admin.php?e=exists");
        } else if ($Redirect == "edit") {
            header("Location: edit.php?e=exists&action=write");
        } else {
            header("Location: /");
        }

        die();
    }
}

if (isset($_REQUEST['body']) && htmlspecialchars($_REQUEST['body']) != "") {
    $Body = htmlspecialchars($_REQUEST['body']);

    $Hash = hash('sha256', $Body);

    if (!is_dir("$documentLocation/$Hash")) {
        mkdir("$documentLocation/$Hash", 0777, true);
    }

    $File = "$documentLocation/$Hash/$Hash.md";

    if ($File == '') {
        print("File parameter cannot be empty.");
        die();
    }

    if (!file_put_contents($File, $Body)) {
        if ($Redirect == "admin") {
            header("Location: admin.php?e=file");
        } else if ($Redirect == "edit") {
            header("Location: edit.php?e=file&action=write");
        } else {
            header("Location: /");
        }

        die();
    }
} else {
    if ($Redirect == "admin") {
        header("Location: admin.php?e=file");
    } else if ($Redirect == "edit") {
        header("Location: edit.php?e=file&action=write");
    } else {
        header("Location: /");
    }

    die();
}

$Database->exec("INSERT INTO pages(username, date, endpoint, file) VALUES('$Username', '$Date', '$Endpoint', '$File')");

if ($Redirect == "admin") {
    header("Location: admin.php?action=users");
} else if ($Redirect == "edit") {
    header("Location: edit.php?action=write&e=saved");
} else {
    header("Location: /");
}

die();

?>
