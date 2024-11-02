<?php session_start();
include "core.php";
include "config.php";

// fields
$Date = "";
$Endpoint = "";
$File = "";
$id = -1;

$noHist = false;
$Request = "false";

$Redirect = "";
$AuthorizedCreation = 0;

if (isset($_REQUEST['redir'])) {
    $Redirect = htmlspecialchars($_REQUEST['redir']);
}
if (isset($_REQUEST['request'])) {
    $Request = htmlspecialchars($_REQUEST['request']);
}

if (isset($_REQUEST['id'])) {
    $id = htmlspecialchars($_REQUEST['id']);
} else {
    if ($Redirect == "admin") {
        header("Location: admin.php?e=endpoint");
    } else if ($Redirect == "edit") {
        header("Location: edit.php?e=endpoint&action=articles");
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

$DatabaseQuery = $Database->query('SELECT * FROM pages');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['id'] == $id && $id != -1) {
        $File = $line['file'];
    }
    if ($line['endpoint'] == $Endpoint && $line['id'] != $id) {
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

$OldFile = "";

if (isset($_REQUEST['body']) && htmlspecialchars($_REQUEST['body']) != "") {
    $Body = htmlspecialchars($_REQUEST['body']);

    if ($File == '') {
        print("File parameter cannot be empty. $id");
        die();
    }

    // back up the old file first
    $OldFileContents = file_get_contents($File);
    $Hash = hash('sha256', $OldFileContents);
    $OldFile = "$historyLocation/$Hash/$Hash.md";

    if (file_exists($OldFile)) {
        $noHist = true;
    } else {
        if (!is_dir("$historyLocation/$Hash")) {
            mkdir("$historyLocation/$Hash", 0777, true);
        }
        if (!file_put_contents($OldFile, $OldFileContents)) {
            if ($Redirect == "admin") {
                header("Location: admin.php?e=ofile");
            } else if ($Redirect == "edit") {
                header("Location: edit.php?e=ofile&action=articles");
            } else {
                header("Location: /");
            }

            die();
        }
    }

    // now write to the new file
    if (!file_put_contents($File, $Body)) {
        if ($Redirect == "admin") {
            header("Location: admin.php?e=file");
        } else if ($Redirect == "edit") {
            header("Location: edit.php?e=file&action=articles");
        } else {
            header("Location: /");
        }

        die();
    }

    // also delete requests
    if ($Request == "true") {
        $ModDatabaseQuery = $Database->query('SELECT * FROM requests');
        while ($mline = $ModDatabaseQuery->fetchArray()) {
            if ($mline['pageid'] == $id && $id != -1) {
                $File = $mline['file'];
                $Directory = dirname($File);

                if (is_file($File)) {
                    unlink($File);

                    if (is_dir($Directory)) {
                        rmdir($Directory);
                    }
                }

                $Database->exec("DELETE FROM requests WHERE pageid='$id'");

                break;
            }
        }
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

$Database->exec("UPDATE pages SET date='$Date' WHERE id='$id'");
$Database->exec("UPDATE pages SET endpoint='$Endpoint' WHERE id='$id'");

if ($noHist == false) {
    $Database->exec("INSERT INTO history(username, pageid, date, endpoint, file) VALUES('$Username', '$id', '$Date', '$Endpoint', '$OldFile')");
}

if ($Redirect == "admin") {
    header("Location: admin.php?action=users");
} else if ($Redirect == "edit") {
    header("Location: edit.php?action=write&id=$id&e=saved");
} else {
    header("Location: /");
}

die();

?>
