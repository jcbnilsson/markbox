<?php session_start();
include "core.php";
include "config.php";

// fields
$Date = "";
$Endpoint = "";
$Message = "";
$id = -1;

$Redirect = "";
$Authorized = 0;

if (isset($_REQUEST['redir'])) {
    $Redirect = htmlspecialchars($_REQUEST['redir']);
}

if (isset($_REQUEST['id'])) {
    $id = htmlspecialchars($_REQUEST['id']);
} else {
    header("Location: /");
    die();
}

if (isset($_REQUEST['message'])) {
    $Message = htmlspecialchars($_REQUEST['message']);
}

$Database = createTables($sqlDB);
$DatabaseQuery = $Database->query('SELECT * FROM users');

if (!isset($_SESSION['username']) || !isset($_SESSION['password']) || !isset($_SESSION['type'])) {
    header('Location: login.php');
    die();
}

$DatabaseQuery = $Database->query('SELECT * FROM users');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['username'] == htmlspecialchars($_SESSION['username']) && htmlspecialchars($_SESSION['username']) != "" && $line['password'] == htmlspecialchars($_SESSION['password'])) {
        $Authorized = 1;
        break;
    }
}

$Username = htmlspecialchars($_SESSION['username']);

// not authorized
if ($Authorized != 1) {
    header('Location: /');
    die();
}

// date must be specified
if (isset($_REQUEST['date']) && (htmlspecialchars($_REQUEST['date']) != "")) {
    $Date = htmlspecialchars($_REQUEST['date']);
} else {
    $Date = date($dateFormat);
}

$DatabaseQuery = $Database->query('SELECT * FROM pages');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['id'] == $id && $id != -1) {
        $Endpoint = $line['endpoint'];
    }
}

if (isset($_REQUEST['body']) && htmlspecialchars($_REQUEST['body']) != "") {
    $Body = htmlspecialchars($_REQUEST['body']);

    $Hash = hash('sha256', $Body);

    if (!is_dir("$requestLocation/$Hash")) {
        mkdir("$requestLocation/$Hash", 0777, true);
    }

    $File = "$requestLocation/$Hash/$Hash.md";

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
        header("Location: edit.php?e=file&action=articles");
    } else {
        header("Location: /");
    }

    die();
}

$Database->exec("INSERT INTO requests(pageid, username, date, message, endpoint, file) VALUES('$id', '$Username', '$Date', '$Message', '$Endpoint', '$File')");

if ($Redirect == "admin") {
    header("Location: admin.php?action=users");
} else if ($Redirect == "edit") {
    header("Location: edit.php?action=write&id=$id&e=saved");
} else {
    header("Location: /");
}

die();

?>
