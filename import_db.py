#!/usr/bin/env python3
"""Import SQL file to Railway MySQL database."""
import pymysql

# Railway MySQL public connection
HOST = "crossover.proxy.rlwy.net"
PORT = 10687
USER = "root"
PASSWORD = "koRQcehzELnGBGKEwOVvqFVYwHDMTsNp"
DATABASE = "railway"

SQL_FILE = "site_demo/timecafe.sql"

def import_sql():
    print(f"Connecting to {HOST}:{PORT}...")
    conn = pymysql.connect(
        host=HOST,
        port=PORT,
        user=USER,
        password=PASSWORD,
        database=DATABASE,
        charset='utf8mb4',
        autocommit=True
    )
    cursor = conn.cursor()

    print(f"Reading SQL file: {SQL_FILE}")
    with open(SQL_FILE, 'r', encoding='utf-8') as f:
        sql_content = f.read()

    # Split by statement delimiter and execute each
    statements = sql_content.split(';')
    total = len(statements)
    success = 0
    errors = 0

    print(f"Executing {total} statements...")
    for i, stmt in enumerate(statements):
        stmt = stmt.strip()
        if not stmt or stmt.startswith('--') or stmt.startswith('/*'):
            continue
        try:
            cursor.execute(stmt)
            success += 1
            if success % 100 == 0:
                print(f"  Progress: {success} statements executed...")
        except Exception as e:
            errors += 1
            if errors < 10:
                print(f"  Error in statement {i}: {str(e)[:100]}")

    print(f"\nDone! Success: {success}, Errors: {errors}")

    # Show tables
    cursor.execute("SHOW TABLES")
    tables = cursor.fetchall()
    print(f"\nTables in database ({len(tables)}):")
    for t in tables:
        print(f"  - {t[0]}")

    cursor.close()
    conn.close()

if __name__ == "__main__":
    import_sql()
