<?php session_start();

include "core.php";
include "config.php";

$Action = "";
$Authorized = 0;
$Primary = 0;
$postID = -1;
$Error = "";
$History = "false";
$Request = "false";

if (!isset($_SESSION['username']) || !isset($_SESSION['password']) || !isset($_SESSION['type'])) {
    header('Location: login.php?redir=edit');
    die();
} else if (htmlspecialchars($_SESSION['type']) != 2) { // not allowed
    header('Location: /');
    die();
}

if (!isset($_REQUEST['action'])) {
    $Action = "write";
} else {
    $Action = htmlspecialchars($_REQUEST['action']);
}

if (!isset($_REQUEST['history'])) {
    $History = "false";
} else {
    $History = htmlspecialchars($_REQUEST['history']);
}

if (!isset($_REQUEST['request'])) {
    $Request = "false";
} else {
    $Request = htmlspecialchars($_REQUEST['request']);
}

if (!isset($_REQUEST['id'])) {
    $postID = -1;
} else {
    $postID = htmlspecialchars($_REQUEST['id']);
}

if (!isset($_REQUEST['e'])) {
    $Error = "";
} else {
    $Error = htmlspecialchars($_REQUEST['e']);
}

$Database = createTables($sqlDB);
$DatabaseQuery = $Database->query('SELECT * FROM users');

while ($line = $DatabaseQuery->fetchArray()) {
    if ($line['username'] == htmlspecialchars($_SESSION['username']) && htmlspecialchars($_SESSION['username']) != "" && $line['password'] == htmlspecialchars($_SESSION['password']) && htmlspecialchars($_SESSION['password']) != "" && $line['usertype'] == 2) {
        $Authorized = 1;
        $Primary = $line['primaryadmin'];
        break;
    }
}

// not authorized
if ($Authorized != 1) {
    header('Location: /');
    die();
}

$html = "";
$html = printHeader($html, 0);

$html .= "\t\t\t<h1>Page manager</h1>\n";
$html .= "\t\t\t\t<div class=\"page_links\">\n";
$html .= "\t\t\t\t\t<span id=\"pageSpan\" class=\"title\">\n";

if ($Action == "write") {
    $html .= "\t\t\t\t\t\t<a href=\"/edit.php?action=write&id=$postID\" id='sel'>Write</a>\n";
} else {
    $html .= "\t\t\t\t\t\t<a href=\"/edit.php?action=write&id=$postID\">Write</a>\n";
}

if ($Action == "attachments") {
    $html .= "\t\t\t\t\t\t<a href=\"/edit.php?action=attachments&id=$postID\" id='sel'>Attachments</a>\n";
} else {
    $html .= "\t\t\t\t\t\t<a href=\"/edit.php?action=attachments&id=$postID\">Attachments</a>\n";
}

if ($Action == "articles") {
    $html .= "\t\t\t\t\t\t<a href=\"/edit.php?action=articles&id=$postID\" id='sel'>Articles</a>\n";
} else {
    $html .= "\t\t\t\t\t\t<a href=\"/edit.php?action=articles&id=$postID\">Articles</a>\n";
}

if ($Action == "requests") {
    $html .= "\t\t\t\t\t\t<a href=\"/edit.php?action=requests&id=$postID\" id='sel'>Requests</a>\n";
} else {
    $html .= "\t\t\t\t\t\t<a href=\"/edit.php?action=requests&id=$postID\">Requests</a>\n";
}

$html .= "\t\t\t\t\t</span>\n";
$html .= "\t\t\t\t</div>\n";

if ($Action == "write") {
    $defaultText = "@markbox.title = \"Unicorns & Lollipops\";\n@markbox.description = \"My description\";\n@markbox.date = \"1970-01-01\";\n@markbox.allowComments = \"true\";\n\nHello world!";
    $defaultEndpoint = "";

    $DatabaseQuery = $Database->query('SELECT * FROM pages');
    while ($line = $DatabaseQuery->fetchArray()) {
        if ($line['id'] == $postID && $postID != -1) {
            $theFile = $line['file'];

            if (file_exists($theFile)) {
                $defaultText = file_get_contents($theFile);
            }

            $defaultEndpoint = $line['endpoint'];
            break;
        }
    }

    if ($History == "true") {
        $DatabaseQuery = $Database->query('SELECT * FROM history');
        while ($line = $DatabaseQuery->fetchArray()) {
            if ($line['id'] == $postID && $postID != -1) {
                $theFile = $line['file'];

                if (file_exists($theFile)) {
                    $defaultText = file_get_contents($theFile);
                }

                $defaultEndpoint = $line['endpoint'];
                $postID = $line['pageid'];
                break;
            }
        }
    }

    if ($Request == "true") {
        $DatabaseQuery = $Database->query('SELECT * FROM requests');
        while ($line = $DatabaseQuery->fetchArray()) {
            if ($line['id'] == $postID && $postID != -1) {
                $theFile = $line['file'];

                if (file_exists($theFile)) {
                    $defaultText = file_get_contents($theFile);
                }

                $defaultEndpoint = $line['endpoint'];
                $postID = $line['pageid'];
                break;
            }
        }
    }

    $html .= "\t\t\t\t<p class=\"pageWarning\"><strong>Switching tab will delete changes made to the Markdown document. Press 'Save' to avoid this.</strong></p>\n";
    $html .= "\t\t\t\t<form method=\"POST\" class=\"new_form\" action=\"/edit.php\"><input type=\"submit\" value=\"New\"></form>\n";

    if ($postID == -1) {
        $html .= "\t\t\t\t<form method=\"POST\" class=\"pageWriteForm\" action=\"/create.php?redir=edit\" method=\"post\">\n";
    } else {
        if ($Request == "true") {
            $html .= "\t\t\t\t<form method=\"POST\" class=\"pageWriteForm\" action=\"/update.php?redir=edit&id=$postID&request=true\" method=\"post\">\n";
        } else {
            $html .= "\t\t\t\t<form method=\"POST\" class=\"pageWriteForm\" action=\"/update.php?redir=edit&id=$postID\" method=\"post\">\n";
        }
    }

    $html .= "\t\t\t\t\t<br><textarea id=\"pageWriteArea\" class=\"pageWriteArea\" name=\"body\" rows=\"32\" cols=\"98\">$defaultText</textarea>\n";
    $html .= "\t\t\t\t\t<br>\n";
    $html .= "\t\t\t\t\t<br><label for=\"endpoint\">Public location</label>\n";

    if ($defaultEndpoint != "") {
        $html .= "\t\t\t\t\t<input type=\"text\" name=\"endpoint\" value=\"$defaultEndpoint\" placeholder=\"/blog/unicorns-and-lollipops\"><br>\n";
    } else {
        $html .= "\t\t\t\t\t<input type=\"text\" name=\"endpoint\" placeholder=\"/blog/unicorns-and-lollipops\"><br>\n";
    }
    $html .= "\t\t\t\t\t<br><input class=\"saveForm\" type=\"submit\" value=\"Save\">\n";
    $html .= "\t\t\t\t</form>\n";

    // add history button if we're editing an existing page
    if ($defaultEndpoint != "") {
        $html .= "\t\t\t\t\t\t<a href=\"/edit.php?action=history&id=$postID\">History</a>\n";
    }

    // handle errors
    if ($Error == "endpoint") {
        $html .= "\t\t\t\t<p class=\"pageError\">You must specify a valid endpoint (e.g. /blog/article1)</p>\n";
    } else if ($Error == "file") {
        $html .= "\t\t\t\t<p class=\"pageError\">Failed to upload file.</p>\n";
    } else if ($Error == "ofile") {
        $html .= "\t\t\t\t<p class=\"pageError\">Failed to back up file, aborting.</p>\n";
    } else if ($Error == "exists") {
        $html .= "\t\t\t\t<p class=\"pageError\">A file with this endpoint already exists.</p>\n";
    } else if ($Error == "saved") { // not actually an error but i don't want to make this too complicated
        $Date = date($dateFormat) . " at " . date($timeFormat);
        $html .= "\t\t\t\t<p class=\"pageSuccess\">$Date: Your changes have been saved.</p>\n";
    }
} else if ($Action == "attachments") {
    $html .= "\t\t\t\t<form method=\"POST\" class=\"pageFileUploadForm\" action=\"/upload.php?redir=edit\" method=\"post\" enctype=\"multipart/form-data\">\n";
    $html .= "\t\t\t\t\t<br><input type=\"file\" name=\"file\" id=\"file\">\n";
    $html .= "\t\t\t\t\t<input type=\"submit\" value=\"Upload selected file\" id=\"upload\" name=\"upload\">\n";
    $html .= "\t\t\t\t</form>\n";

    $attachments = array();

    if (is_dir($attachmentLocation)) {
        $attachments = scandir($attachmentLocation);
    }

    $html .= "\t\t\t\t<table class=\"pageAttachmentView\">\n";
    $html .= "\t\t\t\t\t<tr class=\"pageAttachmentView\">\n";
    $html .= "\t\t\t\t\t\t<th class=\"pageAttachmentLocation\"></th>\n";
    $html .= "\t\t\t\t\t</tr>\n";


    $html .= "\t\t\t\t\t<tr class=\"pageAttachmentLocation\">\n";

    foreach ($attachments as $index => $file) {
        if ($file == "." || $file == "..") {
            continue;
        }

        $html .= "\t\t\t\t\t<tr class=\"pageAttachmentLocation\">\n";
        $html .= "\t\t\t\t\t\t<td class=\"pageAttachmentLocation\"><a href=\"/$attachmentLocation/$file\">$file</a></td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"pageRemove\"><a href=\"/remove-file.php?redir=edit&file=$index\">Remove</a></td>\n";
        $html .= "\t\t\t\t\t</tr>\n";
    }

    $html .= "\t\t\t\t</table>\n";

    // handle errors
    if ($Error == "endpoint") {
        $html .= "\t\t\t\t<p class=\"pageError\">You must specify a valid endpoint (e.g. /blog/article1)</p>\n";
    } else if ($Error == "file") {
        $html .= "\t\t\t\t<p class=\"pageError\">Failed to upload file.</p>\n";
    } else if ($Error == "ofile") {
        $html .= "\t\t\t\t<p class=\"pageError\">Failed to back up file, aborting.</p>\n";
    } else if ($Error == "exists") {
        $html .= "\t\t\t\t<p class=\"pageError\">A file with this endpoint already exists.</p>\n";
    }
} else if ($Action == "history") {
    $html .= "\t\t\t\t<table class=\"historyUserView\">\n";
    $html .= "\t\t\t\t\t<tr class=\"historyArticleView\">\n";
    $html .= "\t\t\t\t\t\t<th class=\"historyUser\">User</th>\n";
    $html .= "\t\t\t\t\t\t<th class=\"historyDate\">Date</th>\n";
    $html .= "\t\t\t\t\t\t<th class=\"historyEndpoint\">Location</th>\n";
    $html .= "\t\t\t\t\t\t<th class=\"historyFile\">File</th>\n";
    $html .= "\t\t\t\t\t</tr>\n";

    $DatabaseQuery = $Database->query('SELECT * FROM history');
    while ($line = $DatabaseQuery->fetchArray()) {
        if ($line['pageid'] != $postID) {
            continue;
        }

        $ID = $line['id'];
        $Username = $line['username'];
        $Date = $line['date'];
        $Endpoint = $line['endpoint'];
        $File = $line['file'];
        $baseFile = basename($File);

        $html .= "\t\t\t\t\t<tr class=\"historyArticleView\">\n";
        $html .= "\t\t\t\t\t\t<td class=\"historyUser\">$Username</td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"historyDate\">$Date</td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"historyEndpoint\"><a href=\"../$Endpoint\">$Endpoint</a></td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"historyFile\"><a href=\"$File\">$baseFile</a></td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"historyRestore\"><a href=\"/edit.php?id=$ID&history=true\">Restore</a></td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"historyRemove\"><a href=\"/remove.php?redir=edit&id=$ID&history=true\">Remove</a></td>\n";

        $html .= "\t\t\t\t\t</tr>\n";
    }

    $html .= "\t\t\t\t</table>\n";
} else if ($Action == "articles") {
    $html .= "\t\t\t\t<table class=\"pageUserView\">\n";
    $html .= "\t\t\t\t\t<tr class=\"pageArticleView\">\n";
    $html .= "\t\t\t\t\t\t<th class=\"pageID\">ID</th>\n";
    $html .= "\t\t\t\t\t\t<th class=\"pageUser\">User</th>\n";
    $html .= "\t\t\t\t\t\t<th class=\"pageDate\">Date</th>\n";
    $html .= "\t\t\t\t\t\t<th class=\"pageEndpoint\">Location</th>\n";
    $html .= "\t\t\t\t\t\t<th class=\"pageFile\">File</th>\n";
    $html .= "\t\t\t\t\t</tr>\n";

    $DatabaseQuery = $Database->query('SELECT * FROM pages');
    while ($line = $DatabaseQuery->fetchArray()) {
        $ID = $line['id'];
        $Username = $line['username'];
        $Date = $line['date'];
        $Endpoint = $line['endpoint'];
        $File = $line['file'];
        $baseFile = basename($File);

        $html .= "\t\t\t\t\t<tr class=\"pageArticleView\">\n";
        $html .= "\t\t\t\t\t\t<td class=\"pageID\" id=\"id-1-$Username\">$ID</td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"pageUser\">$Username</td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"pageDate\">$Date</td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"pageEndpoint\"><a href=\"../$Endpoint\">$Endpoint</a></td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"pageFile\"><a href=\"$File\">$baseFile</a></td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"pageEdit\"><a href=\"/edit.php?id=$ID\">Edit</a></td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"pageRemove\"><a href=\"/remove.php?redir=edit&id=$ID\">Remove</a></td>\n";

        $html .= "\t\t\t\t\t</tr>\n";
    }

    $html .= "\t\t\t\t</table>\n";

    // handle errors
    if ($Error == "endpoint") {
        $html .= "\t\t\t\t<p class=\"pageError\">You must specify a valid endpoint (e.g. /blog/article1)</p>\n";
    } else if ($Error == "file") {
        $html .= "\t\t\t\t<p class=\"pageError\">Failed to upload file.</p>\n";
    } else if ($Error == "ofile") {
        $html .= "\t\t\t\t<p class=\"pageError\">Failed to back up file, aborting.</p>\n";
    } else if ($Error == "exists") {
        $html .= "\t\t\t\t<p class=\"pageError\">A file with this endpoint already exists.</p>\n";
    }
} else if ($Action == "requests") {
    $html .= "\t\t\t\t<table class=\"requestUserView\">\n";
    $html .= "\t\t\t\t\t<tr class=\"requestArticleView\">\n";
    $html .= "\t\t\t\t\t\t<th class=\"requestUser\">User</th>\n";
    $html .= "\t\t\t\t\t\t<th class=\"requestDate\">Date</th>\n";
    $html .= "\t\t\t\t\t\t<th class=\"requestMessage\">Message</th>\n";
    $html .= "\t\t\t\t\t\t<th class=\"requestEndpoint\">Location</th>\n";
    $html .= "\t\t\t\t\t\t<th class=\"requestFile\">File</th>\n";
    $html .= "\t\t\t\t\t</tr>\n";

    $DatabaseQuery = $Database->query('SELECT * FROM requests');
    while ($line = $DatabaseQuery->fetchArray()) {
        if ($line['pageid'] != $postID) {
            continue;
        }

        $ID = $line['id'];
        $Username = $line['username'];
        $Message = $line['message'];
        $Date = $line['date'];
        $Endpoint = $line['endpoint'];
        $File = $line['file'];
        $baseFile = basename($File);

        $Message = truncateText($Message, 50);

        if ($Message == "") {
            $Message = "No message specified.";
        }

        $html .= "\t\t\t\t\t<tr class=\"requestArticleView\">\n";
        $html .= "\t\t\t\t\t\t<td class=\"requestUser\">$Username</td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"requestDate\">$Date</td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"requestMessage\">$Message</td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"requestEndpoint\"><a href=\"../$Endpoint\">$Endpoint</a></td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"requestFile\"><a href=\"$File\">$baseFile</a></td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"requestView\"><a href=\"/edit.php?id=$ID&request=true\">View changes</a></td>\n";
        $html .= "\t\t\t\t\t\t<td class=\"requestDeny\"><a href=\"/remove.php?redir=edit&id=$ID&request=true\">Deny</a></td>\n";

        $html .= "\t\t\t\t\t</tr>\n";
    }

    $html .= "\t\t\t\t</table>\n";
}

$html = printFooter($html);

print "$html";

?>
