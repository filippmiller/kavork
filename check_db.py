#!/usr/bin/env python3
"""Check Railway MySQL database tables."""
import pymysql

# Railway MySQL public connection
HOST = "crossover.proxy.rlwy.net"
PORT = 10687
USER = "root"
PASSWORD = "koRQcehzELnGBGKEwOVvqFVYwHDMTsNp"
DATABASE = "railway"

def check_db():
    print(f"Connecting to {HOST}:{PORT}...")
    conn = pymysql.connect(
        host=HOST,
        port=PORT,
        user=USER,
        password=PASSWORD,
        database=DATABASE,
        charset='utf8mb4'
    )
    cursor = conn.cursor()

    cursor.execute("SHOW TABLES")
    tables = cursor.fetchall()
    print(f"\nTables in database ({len(tables)}):")
    for t in tables:
        print(f"  - {t[0]}")

    cursor.close()
    conn.close()

if __name__ == "__main__":
    check_db()
