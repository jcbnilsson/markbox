<?php session_start();
include "config.php";
include "core.php";

if (!$publicAccountCreation) {
    header("Location: /");
    die();
}

if (isset($_REQUEST['username']) && isset($_REQUEST['password'])) {
    $Username = htmlspecialchars($_REQUEST['username']);
    $Password = generatePassword(htmlspecialchars($_REQUEST['password']));

    if (htmlspecialchars($_REQUEST['password']) != htmlspecialchars($_REQUEST['cpassword'])) {
        header("Location: register.php?e=mismatch");
        die();
    }

    if ($storeAgent || $storeAgent == "true") $userAgent = getUserAgent();
    if ($storeCreated || $storeCreated == "true") $Created = date($dateFormat);
    if ($storeLastUsage || $storeLastUsage == "true") $lastUsed = date($dateFormat);
    if ($storeIP || $storeIP == "true") $ip = getIPAddress();

    // check if a user by the same name already exists
    $ipAddresses = 0;
    $Database = createTables($sqlDB);
    $DatabaseQuery = $Database->query('SELECT * FROM users');
    while ($line = $DatabaseQuery->fetchArray()) {
        if ($storeIP || $storeIP == "true") {
            if ($line['ip'] == $ip) {
                $ipAddresses++;
            }
        }
        if ($line['username'] == "$Username" && $Username != "" && $line['username'] != "") {
            header("Location: register.php?e=exists");
            die();
        }
    }

    if ($storeIP || $storeIP == "true") {
        if ($ipAddresses > $maxAccountsPerIP) {
            header("Location: register.php?e=limit");
            die();
        }
    }

    $Key = hash('sha256', rand());

    $Database->exec("INSERT INTO users(username, password, usertype, primaryadmin, numberofcomments, lastused, created, ip, useragent, key) VALUES('$Username', '$Password', '1', '0', '0', '$lastUsed', '$Created', '$ip', '$userAgent', '$Key')");

    header("Location: login.php");
    die();
} else {
    $html = "";

    $html = printHeader($html, 0);

    $html .= "\t\t\t<h1 id='registerHeader'>Welcome to $instanceName</h1>\n";
    $html .= "\t\t\t\t<p>To create an account, enter your desired user name and password.</p>\n";
    $html .= "\t\t\t\t<form method=\"POST\" action=\"register.php\">\n";
    $html .= "\t\t\t\t\t<input type=\"text\" name=\"username\" placeholder=\"Username\">\n";
    $html .= "\t\t\t\t\t<input type=\"password\" name=\"password\" placeholder=\"Password\">\n";
    $html .= "\t\t\t\t\t<input type=\"password\" name=\"cpassword\" placeholder=\"Confirm password\">\n";
    if (isset($Redirect)) $html .= "\t\t\t\t\t<input type=\"hidden\" name=\"redir\" value=\"$Redirect\">\n";
    $html .= "\t\t\t\t\t<input type=\"submit\" value=\"Create account\">\n";
    $html .= "\t\t\t\t</form>\n";

    if (isset($_REQUEST['e']) && htmlspecialchars($_REQUEST['e']) == "exists") {
        session_unset();
        session_destroy();

        $html .= "\t\t\t\t<p class=\"error\">An account by this name already exists.</p>\n";
    } else if (isset($_REQUEST['e']) && htmlspecialchars($_REQUEST['e']) == "mismatch") {
        session_unset();
        session_destroy();

        $html .= "\t\t\t\t<p class=\"error\">The two passwords do not match.</p>\n";
    } else if (isset($_REQUEST['e']) && htmlspecialchars($_REQUEST['e']) == "limit") {
        session_unset();
        session_destroy();

        $html .= "\t\t\t\t<p class=\"error\">Calm down. You've created too many accounts.</p>\n";
    }

    $html = printFooter($html);

    print "$html";
}
?>
