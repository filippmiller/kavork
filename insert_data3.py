#!/usr/bin/env python3
"""Insert essential data."""
import pymysql

HOST = "crossover.proxy.rlwy.net"
PORT = 10687
USER = "root"
PASSWORD = "koRQcehzELnGBGKEwOVvqFVYwHDMTsNp"
DATABASE = "railway"

def main():
    print("Connecting to MySQL...")
    conn = pymysql.connect(
        host=HOST, port=PORT, user=USER, password=PASSWORD,
        database=DATABASE, charset='utf8mb4', autocommit=True
    )
    cursor = conn.cursor()

    print("Inserting franchisee_tariffs data...")
    try:
        cursor.execute("""
            INSERT INTO `franchisee_tariffs` (`id`, `name`, `description`, `label`, `cafe_count`, `roles`, `day_price`, `days_period`, `active`, `created_at`)
            VALUES
            (1, 'Small', 'Up to 2 venues', 1, 2, '', 0.99, 30, 1, '1570437649'),
            (2, 'Medium', 'Up to 5 venues', 2, 5, '', 1.99, 30, 1, '1570437649'),
            (3, 'Large', 'Up to 10 venues', 3, 10, '', 2.99, 30, 1, '1570437649')
            ON DUPLICATE KEY UPDATE name=VALUES(name)
        """)
        print("  Done")
    except Exception as e:
        print(f"  Error: {e}")

    cursor.execute("SELECT * FROM franchisee_tariffs")
    rows = cursor.fetchall()
    print(f"\nfranchisee_tariffs rows: {len(rows)}")
    for row in rows:
        print(f"  {row}")

    cursor.close()
    conn.close()

if __name__ == "__main__":
    main()
