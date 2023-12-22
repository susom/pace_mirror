<?php

namespace Stanford\PACE;

/** @var PACE $module */

$startTS = microtime(true);
$type = "cron";

if(isset($_GET["manual"])) {
    $module->logManualTrigger();
    $type = "manual";
} else {
    $module->logCronTrigger();
}

$module->mirrorRhapsode($type);
$time = round(microtime(true) - $startTS, 3);
$module->emLog("[" . $module->getProjectId() . "]" . " Duration of run: " . ($time) . " of type $type");

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Manual Trigger Info</title>
</head>
<body>
    <p><?php echo "Rhapsode mirror finished, Elapsed time $time seconds"; ?></p>
    <button class="button btn-primary" onclick="history.back()">Back to project page</button>
</body>
</html>
