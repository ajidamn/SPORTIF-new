<?php
$sql = file_get_contents('schema_only.sql');

preg_match_all('/CREATE TABLE `(\w+)` \((.*?)\) ENGINE/s', $sql, $tables);
preg_match_all('/ALTER TABLE `(\w+)`\s+ADD CONSTRAINT `.*?` FOREIGN KEY \(`(\w+)`\) REFERENCES `(\w+)` \(`(\w+)`\)/', $sql, $fks, PREG_SET_ORDER);
preg_match_all('/ALTER TABLE `(\w+)`\s+ADD PRIMARY KEY \(`(.*?)`\)/', $sql, $pks, PREG_SET_ORDER);

$primaryKeys = [];
foreach ($pks as $pk) {
    $table = $pk[1];
    $cols = explode(',', str_replace('`', '', $pk[2]));
    foreach($cols as $c) {
        $primaryKeys[$table][] = trim($c);
    }
}

$dbml = "// Use DBML to define your database structure\n// Docs: https://dbml.dbdiagram.io/docs\n\n";

for ($i = 0; $i < count($tables[0]); $i++) {
    $table = $tables[1][$i];
    $cols = $tables[2][$i];
    
    $dbml .= "Table {$table} {\n";
    $lines = explode("\n", $cols);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '`') === 0) {
            
            preg_match('/`([^`]+)`\s+([a-zA-Z0-9_]+(?:\([^)]+\))?)(.*)/', $line, $matches);
            
            if (count($matches) >= 3) {
                $colName = $matches[1];
                $colTypeRaw = $matches[2];
                $restOfLine = $matches[3]; 
                $restOfLineUpper = strtoupper($restOfLine);
                
                $colType = strtolower($colTypeRaw);
                $enumValues = '';
                
                if (strpos($colType, 'enum') !== false) {
                    if (preg_match('/enum\((.*?)\)/i', $colTypeRaw, $enumMatch)) {
                        $enumValues = str_replace("'", "", $enumMatch[1]); 
                    }
                    $colType = 'varchar'; 
                } elseif (strpos($colType, '(') !== false) {
                    $colType = explode('(', $colType)[0];
                }
                
                $settings = [];
                if (isset($primaryKeys[$table]) && in_array($colName, $primaryKeys[$table])) {
                    $settings[] = "primary key";
                } elseif (strpos($restOfLineUpper, 'PRIMARY KEY') !== false) {
                    $settings[] = "primary key";
                }
                
                if (strpos($restOfLineUpper, 'NOT NULL') !== false) {
                    $settings[] = "not null";
                }
                
                if (preg_match('/DEFAULT\s+(\'[^\']+\'|`[^`]+`|[^\s,]+)/i', $restOfLine, $defMatches)) {
                    $def = $defMatches[1];
                    // dbdiagram requires functions to be wrapped in backticks
                    if (strpos($def, '()') !== false || strtolower($def) === 'current_timestamp' || strtolower($def) === 'current_timestamp()') {
                        $def = "`{$def}`";
                    }
                    $settings[] = "default: {$def}";
                }
                
                $notes = [];
                if (preg_match('/COMMENT\s+\'([^\']+)\'/i', $line, $comMatches)) {
                     $notes[] = str_replace("'", "", $comMatches[1]);
                }
                if ($enumValues !== '') {
                     $notes[] = "Enum values: {$enumValues}";
                }
                
                if (count($notes) > 0) {
                     $combinedNote = implode(' | ', $notes);
                     // Using triple quotes is safer for dbdiagram notes
                     $settings[] = "note: '''{$combinedNote}'''";
                }
                
                $settingsStr = count($settings) > 0 ? " [" . implode(', ', $settings) . "]" : "";
                
                $dbml .= "  {$colName} {$colType}{$settingsStr}\n";
            }
        }
    }
    $dbml .= "}\n\n";
}

foreach ($fks as $fk) {
    $table = $fk[1];
    $col = $fk[2];
    $refTable = $fk[3];
    $refCol = $fk[4];
    $dbml .= "Ref: {$table}.{$col} > {$refTable}.{$refCol}\n";
}

file_put_contents('dbdiagram.dbml', $dbml);
echo "DBML generated.";
