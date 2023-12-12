<?php

namespace Stanford\PACE;

/** @var PACE $module */

$startTS = microtime(true);

$module->mirrorRhapsode();
$time = round(microtime(true) - $startTS, 3);
$module->emLog("[" . $module->getProjectId() . "]" . " Duration of run: " . ($time));

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
</head>
<body>
    <p><?php echo "Rhapsode mirror finished, Elapsed time $time seconds"; ?></p>
    <button class="button btn-primary" onclick="history.back()">Back to project page</button>
</body>
</html>
