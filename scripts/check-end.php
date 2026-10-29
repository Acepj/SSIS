<?php
$f = file('C:/Users/john paul/OneDrive/Documents/Coding/SSIS/backend/src/Services/DocumentRequestService.php');
echo 'total lines: '.count($f)."\n";
echo "last 15:\n";
for ($i = count($f) - 15; $i < count($f); $i++) {
    echo ($i+1)." | ".trim($f[$i])."\n";
}
