<?php session_start();
include "core.php";
include "config.php";

$id = -1;

$History = "false";
$Request = "false";
$Redirect = "";
$AuthorizedCreation = 0;

if (isset($_REQUEST['redir'])) {
    $Redirect = htmlspecialchars($_REQUEST['redir']);
}

if (isset($_REQUEST['history'])) {
    $History = htmlspecialchars($_REQUEST['history']);
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

if ($History == "false" && $Request == "false") {
    $DatabaseQuery = $Database->query('SELECT * FROM pages');
    while ($line = $DatabaseQuery->fetchArray()) {
        if ($line['id'] == $id && $id != -1) {
            $File = $line['file'];
            $Directory = dirname($File);

            if (is_file($File)) {
                unlink($File);

                if (is_dir($Directory)) {
                    rmdir($Directory);
                }
            }

            $Database->exec("DELETE FROM pages WHERE id='$id'");

            // also delete requests
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

            // also delete history
            $HistDatabaseQuery = $Database->query('SELECT * FROM history');
            while ($hline = $HistDatabaseQuery->fetchArray()) {
                if ($hline['pageid'] == $id && $id != -1) {
                    $File = $hline['file'];
                    $Directory = dirname($File);

                    if (is_file($File)) {
                        unlink($File);

                        if (is_dir($Directory)) {
                            rmdir($Directory);
                        }
                    }

                    $Database->exec("DELETE FROM history WHERE pageid='$id'");

                    break;
                }
            }

            break;
        }
    }
} else if ($Request == "true") {
    $DatabaseQuery = $Database->query('SELECT * FROM requests');
    while ($line = $DatabaseQuery->fetchArray()) {
        if ($line['id'] == $id && $id != -1) {
            $File = $line['file'];
            $Directory = dirname($File);

            if (is_file($File)) {
                unlink($File);

                if (is_dir($Directory)) {
                    rmdir($Directory);
                }
            }

            $Database->exec("DELETE FROM requests WHERE id='$id'");

            break;
        }
    }
} else if ($History == "true") {
    $DatabaseQuery = $Database->query('SELECT * FROM history');
    while ($line = $DatabaseQuery->fetchArray()) {
        if ($line['id'] == $id && $id != -1) {
            $File = $line['file'];
            $Directory = dirname($File);

            if (is_file($File)) {
                unlink($File);

                if (is_dir($Directory)) {
                    rmdir($Directory);
                }
            }

            $Database->exec("DELETE FROM history WHERE id='$id'");

            break;
        }
    }
}

if ($Redirect == "admin") {
    header("Location: admin.php?action=users");
} else if ($Redirect == "edit") {
    if ($History == "true") {
        header("Location: edit.php?action=history&id=$id");
    } else if ($Request == "true") {
        header("Location: edit.php?action=requests&id=$id");
    } else {
        header("Location: edit.php?action=articles");
    }
} else {
    header("Location: /");
}

die();

?>
