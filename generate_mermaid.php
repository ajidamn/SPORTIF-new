<?php

$sql = file_get_contents('schema_only.sql');

preg_match_all('/CREATE TABLE `(\w+)` \((.*?)\) ENGINE/s', $sql, $tables);
preg_match_all('/ALTER TABLE `(\w+)`\s+ADD CONSTRAINT `.*?` FOREIGN KEY \(`(\w+)`\) REFERENCES `(\w+)` \(`(\w+)`\)/', $sql, $fks, PREG_SET_ORDER);

$mermaid = "erDiagram\n";

for ($i = 0; $i < count($tables[0]); $i++) {
    $table = $tables[1][$i];
    $cols = $tables[2][$i];
    
    $mermaid .= "    {$table} {\n";
    $lines = explode("\n", $cols);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '`') === 0) {
            $parts = explode(' ', $line);
            $colName = str_replace('`', '', $parts[0]);
            $colType = $parts[1];
            if (strpos($colType, '(') !== false) {
                $colType = explode('(', $colType)[0];
            }
            $mermaid .= "        {$colType} {$colName}\n";
        }
    }
    $mermaid .= "    }\n\n";
}

foreach ($fks as $fk) {
    $table = $fk[1];
    $col = $fk[2];
    $refTable = $fk[3];
    $refCol = $fk[4];
    $mermaid .= "    {$refTable} ||--o{ {$table} : \"{$col}\"\n";
}

file_put_contents('mermaid.txt', $mermaid);
echo "Mermaid generated.";
