#!/usr/bin/env python3
"""Check if franchisee_tariffs table exists."""
import pymysql

HOST = "crossover.proxy.rlwy.net"
PORT = 10687
USER = "root"
PASSWORD = "koRQcehzELnGBGKEwOVvqFVYwHDMTsNp"
DATABASE = "railway"

conn = pymysql.connect(
    host=HOST, port=PORT, user=USER, password=PASSWORD, database=DATABASE
)
cursor = conn.cursor()

# Check for franchisee tables
cursor.execute("SHOW TABLES LIKE '%franchis%'")
print("Tables matching 'franchis':", cursor.fetchall())

cursor.execute("SHOW TABLES LIKE '%tariff%'")
print("Tables matching 'tariff':", cursor.fetchall())

# Get all tables
cursor.execute("SHOW TABLES")
print("\nAll tables:")
for t in cursor.fetchall():
    print(f"  {t[0]}")

cursor.close()
conn.close()
