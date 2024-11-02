<?php session_start();
include "core.php";
include "config.php";

// fields
$Username = "";
$Password = "";
$lastUsed = "";
$Created = "";
$ip = "";
$userAgent = "";

$Redirect = "";
$AuthorizedCreation = 0;
$AdminIsPrimary = 0;
$firstUser = 0;
$typeNum = 1;
$numberOfComments = 0;

if (isset($_REQUEST['redir'])) {
    $Redirect = htmlspecialchars($_REQUEST['redir']);
}

$Database = createTables($sqlDB);
$DatabaseQuery = $Database->query('SELECT * FROM users');

if (!checkIfAdminExists()) {
    $firstUser = 1;
} else {
    if (!isset($_SESSION['username']) || !isset($_SESSION['password']) || !isset($_SESSION['type'])) {
        header('Location: login.php?redir=admin');
        die();
    } else if (htmlspecialchars($_SESSION['type']) != 2) { // not allowed
        header('Location: /');
        die();
    }

    $firstUser = 0;
}

$DatabaseQuery = $Database->query('SELECT * FROM users');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['username'] == htmlspecialchars($_SESSION['username']) && htmlspecialchars($_SESSION['username']) != "" && $line['password'] == htmlspecialchars($_SESSION['password']) && $line['usertype'] == htmlspecialchars($_SESSION['type'])) {
        $AuthorizedCreation = 1;
        $AdminIsPrimary = $line['primaryadmin'];
        break;
    }
}

// not authorized
if ($AuthorizedCreation != 1 && $firstUser != 1) {
    header('Location: /');
    die();
}

// username must be specified
if (isset($_REQUEST['username']) && htmlspecialchars($_REQUEST['username']) != "") {
    $Username = htmlspecialchars($_REQUEST['username']);
} else {
    if ($Redirect == "admin") {
        header("Location: admin.php?action=create&e=username");
    } else if ($Redirect == "setup") {
        header("Location: setup.php?e=username");
    } else {
        header("Location: /");
    }

    die();
}

// password must be specified
if (isset($_REQUEST['password']) && (htmlspecialchars($_REQUEST['password']) != "" && $firstUser == 1 || $firstUser != 1)) {
    $Password = generatePassword(htmlspecialchars($_REQUEST['password']));
} else {
    if ($Redirect == "admin") {
        header("Location: admin.php?action=create&e=password");
    } else if ($Redirect == "setup") {
        header("Location: setup.php?e=password");
    } else {
        header("Location: /");
    }

    die();
}

// type must be specified
if (isset($_REQUEST['type']) && htmlspecialchars($_REQUEST['type']) != "") {
    $Type = htmlspecialchars($_REQUEST['type']);
} else {
    if ($Redirect == "admin") {
        header("Location: admin.php?action=create&e=type");
    } else if ($Redirect == "setup") {
        header("Location: setup.php?e=type");
    } else {
        header("Location: /");
    }

    die();
}

// only primary admins may create admin users
if ($AdminIsPrimary != 1 && $firstUser != 1 && $Type == "Admin") {
    if ($Redirect == "admin") {
        header("Location: admin.php?action=create&e=denied");
    } else if ($Redirect == "setup") {
        header("Location: setup.php?e=denied");
    } else {
        header("Location: /");
    }

    die();
}

// check if a user by the same name already exists
$DatabaseQuery = $Database->query('SELECT * FROM users');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['username'] == "$Username" && $Username != "" && $line['username'] != "") {
        if ($Redirect == "admin") {
            header("Location: admin.php?action=create&e=exists");
        } else if ($Redirect == "setup") {
            header("Location: setup.php?e=exists");
        } else {
            header("Location: /");
        }

        die();
    }
}

if ($storeAgent || $storeAgent == "true") $userAgent = getUserAgent();
if ($storeCreated || $storeCreated == "true") $Created = date($dateFormat);
if ($storeLastUsage || $storeLastUsage == "true") $lastUsed = date($dateFormat);
if ($storeIP || $storeIP == "true") $ip = getIPAddress();

if ($Type == "Admin") {
    $typeNum = 2;
} else {
    $typeNum = 1;
}

$Key = hash('sha256', rand());

$Database->exec("INSERT INTO users(username, password, usertype, primaryadmin, numberofcomments, lastused, created, ip, useragent, key) VALUES('$Username', '$Password', '$typeNum', '$firstUser', '$numberOfComments', '$lastUsed', '$Created', '$ip', '$userAgent', '$Key')");

if ($Redirect == "admin") {
    header("Location: admin.php?action=users");
} else {
    header("Location: /");
}

?>
