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

    # Insert franchisee_tariffs data
    print("Inserting franchisee_tariffs data...")
    try:
        cursor.execute("""
            INSERT INTO `franchisee_tariffs` (`id`, `name`, `description`, `label`, `cafe_count`, `roles`, `day_price`, `days_period`, `active`, `created_at`)
            VALUES
            (1, 'Small', 'Up to 2 venues', 'small', 2, NULL, '0.99', 30, 1, 1570437649),
            (2, 'Medium', 'Up to 5 venues', 'medium', 5, NULL, '1.99', 30, 1, 1570437649),
            (3, 'Large', 'Up to 10 venues', 'large', 10, NULL, '2.99', 30, 1, 1570437649),
            (4, 'Enterprise', 'Unlimited venues', 'enterprise', 999, NULL, '4.99', 30, 1, 1570437649)
            ON DUPLICATE KEY UPDATE name=name
        """)
        print("  Done")
    except Exception as e:
        print(f"  Error: {e}")

    # Check data
    cursor.execute("SELECT COUNT(*) FROM franchisee_tariffs")
    count = cursor.fetchone()[0]
    print(f"franchisee_tariffs rows: {count}")

    cursor.execute("SHOW TABLES")
    print(f"\nTotal tables: {len(cursor.fetchall())}")

    cursor.close()
    conn.close()

if __name__ == "__main__":
    main()
