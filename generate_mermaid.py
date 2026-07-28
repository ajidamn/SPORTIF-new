import re

with open('schema_only.sql', 'r', encoding='utf-8') as f:
    sql = f.read()

# Extract tables
tables = re.findall(r'CREATE TABLE `(\w+)` \((.*?)\) ENGINE', sql, re.DOTALL)
fks = re.findall(r'ALTER TABLE `(\w+)`\s+ADD CONSTRAINT `.*?` FOREIGN KEY \(`(\w+)`\) REFERENCES `(\w+)` \(`(\w+)`\)', sql)

mermaid = "erDiagram\n"

for table, cols in tables:
    mermaid += f"    {table} {{\n"
    # parse cols
    lines = cols.split('\n')
    for line in lines:
        line = line.strip()
        if line.startswith('`'):
            parts = line.split(' ')
            col_name = parts[0].replace('`', '')
            col_type = parts[1]
            if '(' in col_type:
                col_type = col_type.split('(')[0]
            mermaid += f"        {col_type} {col_name}\n"
    mermaid += "    }\n\n"

for fk in fks:
    table, col, ref_table, ref_col = fk
    mermaid += f"    {ref_table} ||--o{{ {table} : \"{col}\"\n"

with open('mermaid.txt', 'w', encoding='utf-8') as f:
    f.write(mermaid)
print("Mermaid generated.")
