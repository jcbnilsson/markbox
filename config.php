<?php

$Stylesheet              = "index.css";
$javaScript              = "index.js";
$Icon                    = "favicon.svg";
$Logo                    = "logo.svg";
$sqlDB                   = "markboxDB.sql";
$storeIP                 = true;
$storeAgent              = true;
$storeCreated            = true;
$storeLastUsage          = true;
$publicAccountCreation   = true;
$allowPasswordChange     = true;
$logoHeaderSize          = 24;
$dateFormat              = "Y/m/d";
$timeFormat              = "h:i:sa";
$instanceName            = "markbox";
$instanceDescription     = "This is a markbox instance.";
$footerText              = "Licensed under the GNU Affero General Public License version 3.0.<br><br>Made in Sweden";
$documentLocation        = "documents/";
$attachmentLocation      = "attachments/";
$requestLocation         = "requests/";
$historyLocation         = "history/";
$maxCommentSize          = 1024;
$maxAccountsPerIP        = 4;

$configFile = "";

if (file_exists("config.ini")) {
    $configFile = "config.ini";
} else if (file_exists("config.def.ini")) {
    $configFile = "config.def.ini";
}

if (!file_exists($configFile)) {
    print "Error: Config file '$configFile' not found.";
    die();
}

// load config file
$configEntries = parse_ini_file($configFile);

if (isset($configEntries['css'])) $Stylesheet = $configEntries['css'];
if (isset($configEntries['favicon'])) $Icon = $configEntries['favicon'];
if (isset($configEntries['logo'])) $Logo = $configEntries['logo'];
if (isset($configEntries['sqldb'])) $sqlDB = $configEntries['sqldb'];
if (isset($configEntries['store_ip'])) $storeIP = $configEntries['store_ip'];
if (isset($configEntries['store_user_agent'])) $storeAgent = $configEntries['store_user_agent'];
if (isset($configEntries['store_created'])) $storeCreated = $configEntries['store_created'];
if (isset($configEntries['store_last_usage'])) $storeLastUsage = $configEntries['store_last_usage'];
if (isset($configEntries['logo_header_size'])) $logoHeaderSize = $configEntries['logo_header_size'];
if (isset($configEntries['date_format'])) $dateFormat = $configEntries['date_format'];
if (isset($configEntries['time_format'])) $timeFormat = $configEntries['time_format'];
if (isset($configEntries['instance_name'])) $instanceName = $configEntries['instance_name'];
if (isset($configEntries['instance_description'])) $instanceDescription = $configEntries['instance_description'];
if (isset($configEntries['document_location'])) $documentLocation = $configEntries['document_location'];
if (isset($configEntries['attachment_location'])) $attachmentLocation = $configEntries['attachment_location'];
if (isset($configEntries['request_location'])) $requestLocation = $configEntries['request_location'];
if (isset($configEntries['history_location'])) $historyLocation = $configEntries['history_location'];
if (isset($configEntries['footer_text'])) $footerText = $configEntries['footer_text'];
if (isset($configEntries['allow_change_username'])) $allowUsernameChange = $configEntries['allow_change_username'];
if (isset($configEntries['allow_change_password'])) $allowPasswordChange = $configEntries['allow_change_password'];
if (isset($configEntries['public_account_create'])) $publicAccountCreation = $configEntries['public_account_create'];
if (isset($configEntries['javascript'])) $javaScript = $configEntries['javascript'];
if (isset($configEntries['max_comment_size'])) $maxCommentSize = $configEntries['max_comment_size'];
if (isset($configEntries['max_accounts_per_ip'])) $maxAccountsPerIP = $configEntries['max_accounts_per_ip'];
?>
