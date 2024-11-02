<?php session_start();

include "core.php";
include "config.php";

$Action = "write";
$Authorized = false;
$postID = -1;

if (!isset($_SESSION['username']) || !isset($_SESSION['password']) || !isset($_SESSION['type'])) {
    header('Location: login.php?redir=edit-page');
    die();
}

if (!isset($_REQUEST['id'])) {
    header("Location: /");
    die();
} else {
    $postID = htmlspecialchars($_REQUEST['id']);
}

$Database = createTables($sqlDB);
$DatabaseQuery = $Database->query('SELECT * FROM users');

while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['username'] == htmlspecialchars($_SESSION['username']) && $_SESSION['username'] != "" && $line['password'] == htmlspecialchars($_SESSION['password']) && htmlspecialchars($_SESSION['password']) != "") {
        $Authorized = true;
        break;
    }
}

// not authorized
if ($Authorized != true) {
    header('Location: /');
    die();
}

$defaultText = "";
$defaultEndpoint = "";

$DatabaseQuery = $Database->query('SELECT * FROM pages');
while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['id'] == $postID && $postID != -1) {
        $theFile = $line['file'];

        if (file_exists($theFile)) {
            $defaultText = file_get_contents($theFile);
        } else {
            header("Location: /");
            die();
        }

        $defaultEndpoint = $line['endpoint'];
        break;
    }
}

if ($defaultEndpoint == "") {
    header("Location: /");
    die();
}

$html = "";
$html = printHeader($html, 0);

$html .= "\t\t\t<h1>Editing '$defaultEndpoint'</h1>\n";
$html .= "\t\t\t\t<form method=\"POST\" class=\"pageWriteForm\" action=\"/post-edit.php?redir=edit-page&id=$postID\" method=\"post\">\n";
$html .= "\t\t\t\t\t<br><textarea id=\"pageWriteArea\" class=\"pageWriteArea\" name=\"body\" rows=\"32\" cols=\"98\">$defaultText</textarea>\n";
$html .= "\t\t\t\t\t<br><br><label for=\"message\">Message</label>\n";
$html .= "\t\t\t\t\t<input type=\"text\" name=\"message\" placeholder=\"Fix typo in blog post\"><br>\n";
$html .= "\t\t\t\t\t<br>\n";
$html .= "\t\t\t\t\t<br><input type=\"submit\" value=\"Request changes\"><br>\n";
$html .= "\t\t\t\t\t<br><strong>By clicking 'Request changes' you will be submitting your changes, which will (if accepted) replace the current state of the page. It is up to the site moderators and/or administrators to accept or deny this request. Please take the license of the original document into account before submitting any changes. Site moderators and/or administrators reserve the right to deny this request, remove and/or alter the content and if deemed appropriate ban users.</strong>\n<br>";
$html .= "\t\t\t\t</form>\n";

$html = printFooter($html);

print "$html";

?>
