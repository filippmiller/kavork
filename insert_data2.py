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

    # Check table structure
    print("Table structure:")
    cursor.execute("DESCRIBE franchisee_tariffs")
    for row in cursor.fetchall():
        print(f"  {row}")

    # Insert data using correct types
    print("\nInserting data...")
    try:
        cursor.execute("""
            INSERT INTO `franchisee_tariffs` (`name`, `description`, `label`, `cafe_count`, `roles`, `day_price`, `days_period`, `active`, `created_at`)
            VALUES
            ('Small', 'Up to 2 venues', 1, 2, NULL, '0.99', 30, 1, 1570437649)
            ON DUPLICATE KEY UPDATE name=name
        """)
        print("  Inserted Small")

        cursor.execute("""
            INSERT INTO `franchisee_tariffs` (`name`, `description`, `label`, `cafe_count`, `roles`, `day_price`, `days_period`, `active`, `created_at`)
            VALUES
            ('Medium', 'Up to 5 venues', 2, 5, NULL, '1.99', 30, 1, 1570437649)
            ON DUPLICATE KEY UPDATE name=name
        """)
        print("  Inserted Medium")

        cursor.execute("""
            INSERT INTO `franchisee_tariffs` (`name`, `description`, `label`, `cafe_count`, `roles`, `day_price`, `days_period`, `active`, `created_at`)
            VALUES
            ('Large', 'Up to 10 venues', 3, 10, NULL, '2.99', 30, 1, 1570437649)
            ON DUPLICATE KEY UPDATE name=name
        """)
        print("  Inserted Large")

    except Exception as e:
        print(f"  Error: {e}")

    cursor.execute("SELECT COUNT(*) FROM franchisee_tariffs")
    count = cursor.fetchone()[0]
    print(f"\nfranchisee_tariffs rows: {count}")

    cursor.close()
    conn.close()

if __name__ == "__main__":
    main()
