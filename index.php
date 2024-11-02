<?php session_start();

include "config.php";
include "core.php";

$Error = "";
$html = "";
$subdir = "";

if (isset($_REQUEST['e'])) $Error = htmlspecialchars($_REQUEST['e']);

if (isset($_GET['endpoint'])) {
    $subdir = $_GET['endpoint'];
} else if (isset($_SERVER['REQUEST_URI'])) {
    $subdir = '/' . trim(strtok($_SERVER['REQUEST_URI'], '?'), '/');
} else {
    $subdir = '/';
}

if (!checkIfAdminExists()) {
    header("Location: setup.php");
    die();
}

$html = printHeader($html, 1); // also prints page content
$html = printFooter($html);

print "$html";

?>
