<?php
$inputFile = 'sportif_db.sql';
$outputFile = 'schema_only.sql';

$inCreate = false;
$inAlter = false;

$out = fopen($outputFile, 'w');
$lines = file($inputFile);

foreach ($lines as $line) {
    if (strpos($line, 'CREATE TABLE') === 0) {
        $inCreate = true;
    }
    if (strpos($line, 'ALTER TABLE') === 0) {
        $inAlter = true;
    }
    
    if ($inCreate || $inAlter) {
        fwrite($out, $line);
        if (strpos(trim($line), ';') === strlen(trim($line)) - 1) {
            $inCreate = false;
            $inAlter = false;
            fwrite($out, "\n");
        }
    }
}
fclose($out);
echo "Done.";
