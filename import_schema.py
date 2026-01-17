#!/usr/bin/env python3
"""Extract and import table schemas from SQL file."""
import pymysql
import re

HOST = "crossover.proxy.rlwy.net"
PORT = 10687
USER = "root"
PASSWORD = "koRQcehzELnGBGKEwOVvqFVYwHDMTsNp"
DATABASE = "railway"
SQL_FILE = "timecafe_docowor.sql"

def extract_creates(sql_content):
    """Extract CREATE TABLE statements."""
    pattern = r'CREATE TABLE `(\w+)`[^;]+;'
    matches = re.findall(pattern, sql_content, re.DOTALL)
    creates = re.findall(pattern.replace(r'(\w+)', r'\w+'), sql_content, re.DOTALL)
    return creates

def main():
    print(f"Reading {SQL_FILE}...")
    with open(SQL_FILE, 'r', encoding='utf-8') as f:
        sql_content = f.read()

    print("Connecting to MySQL...")
    conn = pymysql.connect(
        host=HOST, port=PORT, user=USER, password=PASSWORD,
        database=DATABASE, charset='utf8mb4', autocommit=True,
        connect_timeout=60, read_timeout=300, write_timeout=300
    )
    cursor = conn.cursor()

    # Get existing tables
    cursor.execute("SHOW TABLES")
    existing = set(t[0] for t in cursor.fetchall())
    print(f"Existing tables: {len(existing)}")

    # Extract CREATE TABLE statements
    create_pattern = r'(CREATE TABLE `(\w+)`[^;]+;)'
    creates = re.findall(create_pattern, sql_content, re.DOTALL)
    print(f"Found {len(creates)} CREATE TABLE statements")

    # Create missing tables
    created = 0
    for stmt, table_name in creates:
        if table_name not in existing:
            try:
                cursor.execute(stmt)
                print(f"  Created: {table_name}")
                created += 1
            except Exception as e:
                print(f"  Error creating {table_name}: {str(e)[:80]}")

    print(f"\nCreated {created} new tables")

    # Import franchisee_tariffs data
    print("\nImporting franchisee_tariffs data...")
    insert_pattern = r"(INSERT INTO `franchisee_tariffs`[^;]+;)"
    inserts = re.findall(insert_pattern, sql_content, re.DOTALL)
    for stmt in inserts:
        try:
            cursor.execute(stmt)
            print("  Inserted franchisee_tariffs data")
        except Exception as e:
            print(f"  Error: {str(e)[:80]}")

    # Import franchisee data
    print("\nImporting franchisee data...")
    insert_pattern = r"(INSERT INTO `franchisee` [^;]+;)"
    inserts = re.findall(insert_pattern, sql_content, re.DOTALL)
    for stmt in inserts[:1]:  # Just first insert
        try:
            cursor.execute(stmt)
            print("  Inserted franchisee data")
        except Exception as e:
            print(f"  Error: {str(e)[:80]}")

    # Show final tables
    cursor.execute("SHOW TABLES")
    tables = cursor.fetchall()
    print(f"\nFinal table count: {len(tables)}")

    cursor.close()
    conn.close()

if __name__ == "__main__":
    main()
