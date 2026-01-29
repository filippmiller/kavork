#!/usr/bin/env python3
"""Import date-ranged records from a mysqldump into Railway DB.

Parses INSERT statements and inserts rows in small batches to
avoid connection resets on large statements.
"""
from __future__ import annotations

import gzip
import sys

import pymysql


HOST = "crossover.proxy.rlwy.net"
PORT = 10687
USER = "root"
PASSWORD = "koRQcehzELnGBGKEwOVvqFVYwHDMTsNp"
TARGET_DB = "railway"
DUMP_PATH = r"C:\dev\kavork\timecafe_docowor.2026-01-29.sql.gz"
DATE_START = "2026-01-27 00:00:00"
DATE_END = "2026-01-29 00:00:00"

TARGET_TABLES = {
    "user",
    "visitor",
    "visitor_log",
    "transaction",
    "shop_sale",
    "shop_transaction",
    "user_log",
}
BATCH_SIZE = 200


def connect(db: str | None = None) -> pymysql.connections.Connection:
    return pymysql.connect(
        host=HOST,
        port=PORT,
        user=USER,
        password=PASSWORD,
        database=db,
        charset="utf8mb4",
        autocommit=True,
        connect_timeout=60,
        read_timeout=300,
        write_timeout=300,
    )


def _split_rows(values_blob: str) -> list[str]:
    rows: list[str] = []
    depth = 0
    in_string = False
    escape = False
    start = None
    for idx, ch in enumerate(values_blob):
        if in_string:
            if escape:
                escape = False
            elif ch == "\\":
                escape = True
            elif ch == "'":
                in_string = False
        else:
            if ch == "'":
                in_string = True
            elif ch == "(":
                if depth == 0:
                    start = idx + 1
                depth += 1
            elif ch == ")":
                depth -= 1
                if depth == 0 and start is not None:
                    rows.append(values_blob[start:idx])
                    start = None
    return rows


def _split_fields(row_blob: str) -> list[str]:
    fields: list[str] = []
    in_string = False
    escape = False
    buf: list[str] = []
    for ch in row_blob:
        if in_string:
            buf.append(ch)
            if escape:
                escape = False
            elif ch == "\\":
                escape = True
            elif ch == "'":
                in_string = False
        else:
            if ch == "'":
                in_string = True
                buf.append(ch)
            elif ch == ",":
                fields.append("".join(buf).strip())
                buf = []
            else:
                buf.append(ch)
    fields.append("".join(buf).strip())
    return fields


def _in_range(value: str | None) -> bool:
    if not value:
        return False
    return DATE_START <= value < DATE_END


def _table_columns(cur: pymysql.cursors.Cursor) -> dict[str, list[str]]:
    columns: dict[str, list[str]] = {}
    for table in TARGET_TABLES:
        cur.execute(f"DESCRIBE `{table}`")
        columns[table] = [row[0] for row in cur.fetchall()]
    return columns


def _normalize_id(value: str) -> str:
    value = value.strip()
    if value.startswith("'") and value.endswith("'"):
        return value[1:-1]
    return value


def _fetch_existing_ids(
    cur: pymysql.cursors.Cursor, table: str, date_field: str | None
) -> set[str]:
    if date_field is None:
        return set()
    sql = (
        f"SELECT id FROM `{table}` "
        f"WHERE {date_field} >= '{DATE_START}' AND {date_field} < '{DATE_END}'"
    )
    cur.execute(sql)
    return {str(row[0]) for row in cur.fetchall()}


def insert_range(cur: pymysql.cursors.Cursor) -> list[tuple[str, int]]:
    print("Parsing dump and inserting filtered rows...")
    cur.execute(f"USE `{TARGET_DB}`")
    columns = _table_columns(cur)
    date_field = {
        "user": None,
        "visitor_log": "add_time",
        "transaction": "created_at",
        "shop_sale": "created_at",
        "shop_transaction": "created_at",
        "user_log": "start",
        "visitor": None,
    }

    batches: dict[str, list[str]] = {t: [] for t in TARGET_TABLES}
    inserted: dict[str, int] = {t: 0 for t in TARGET_TABLES}
    visitor_rows: dict[object, str] = {}
    visitor_ids_needed: set[object] = set()
    user_rows: dict[object, str] = {}
    user_ids_needed: set[object] = set()
    existing_ids = {
        table: _fetch_existing_ids(cur, table, date_field[table])
        for table in TARGET_TABLES
        if date_field.get(table)
    }

    def insert_rows(table: str) -> None:
        rows = batches[table]
        if not rows:
            return
        total = 0
        cols = columns[table]
        assignments = ",".join([f"`{col}`=VALUES(`{col}`)" for col in cols])
        for idx in range(0, len(rows), BATCH_SIZE):
            chunk = rows[idx : idx + BATCH_SIZE]
            values_blob = "),(".join(chunk)
            if date_field.get(table):
                sql = (
                    f"INSERT INTO `{table}` VALUES ({values_blob}) "
                    f"ON DUPLICATE KEY UPDATE {assignments}"
                )
            else:
                sql = f"INSERT IGNORE INTO `{table}` VALUES ({values_blob})"
            cur.execute(sql)
            if chunk and cur.rowcount < len(chunk):
                cur.execute("SHOW COUNT(*) WARNINGS")
                warnings_count = cur.fetchone()[0]
                cur.execute("SHOW WARNINGS LIMIT 1")
                warning = cur.fetchone()
                if warning:
                    print(
                        f"  warning {table}: {warning[2]} (warnings={warnings_count})"
                    )
            total += cur.rowcount
        inserted[table] += total
        batches[table] = []

    in_stmt = False
    buf: list[str] = []
    current_table: str | None = None

    with gzip.open(DUMP_PATH, "rt", encoding="utf-8", errors="ignore") as handle:
        for line in handle:
            stripped = line.lstrip()
            if not in_stmt:
                if stripped.startswith("INSERT INTO `"):
                    in_stmt = True
                    buf = [line]
                    start_idx = stripped.find("`") + 1
                    end_idx = stripped.find("`", start_idx)
                    current_table = (
                        stripped[start_idx:end_idx] if end_idx > start_idx else None
                    )
                    if stripped.rstrip().endswith(";"):
                        in_stmt = False
                        statement = "".join(buf)
                        if current_table in TARGET_TABLES:
                            _process_insert(
                                statement,
                                current_table,
                                columns,
                                date_field,
                                batches,
                                existing_ids,
                                visitor_rows,
                                visitor_ids_needed,
                                user_rows,
                                user_ids_needed,
                            )
                        buf = []
                        current_table = None
                else:
                    continue
            else:
                buf.append(line)
                if stripped.rstrip().endswith(";"):
                    in_stmt = False
                    statement = "".join(buf)
                    if current_table in TARGET_TABLES:
                        _process_insert(
                            statement,
                            current_table,
                            columns,
                            date_field,
                            batches,
                            existing_ids,
                            visitor_rows,
                            visitor_ids_needed,
                            user_rows,
                            user_ids_needed,
                        )
                    buf = []
                    current_table = None

    # Insert users referenced by in-range rows.
    if user_ids_needed and user_rows:
        user_ids = {str(v) for v in user_ids_needed}
        existing_user_ids: set[str] = set()
        user_ids_list = list(user_ids)
        for idx in range(0, len(user_ids_list), BATCH_SIZE):
            chunk = user_ids_list[idx : idx + BATCH_SIZE]
            placeholders = ",".join(["%s"] * len(chunk))
            cur.execute(f"SELECT id FROM `user` WHERE id IN ({placeholders})", chunk)
            existing_user_ids.update({str(row[0]) for row in cur.fetchall()})
        for user_id in user_ids_list:
            if user_id in existing_user_ids:
                continue
            row_blob = user_rows.get(user_id)
            if row_blob:
                batches["user"].append(row_blob)

    # Insert visitors referenced by in-range visitor_log rows.
    if visitor_ids_needed and visitor_rows:
        visitor_ids = {str(v) for v in visitor_ids_needed}
        existing_visitor_ids: set[str] = set()
        visitor_ids_list = list(visitor_ids)
        for idx in range(0, len(visitor_ids_list), BATCH_SIZE):
            chunk = visitor_ids_list[idx : idx + BATCH_SIZE]
            placeholders = ",".join(["%s"] * len(chunk))
            cur.execute(
                f"SELECT id FROM `visitor` WHERE id IN ({placeholders})", chunk
            )
            existing_visitor_ids.update({str(row[0]) for row in cur.fetchall()})
        for visitor_id in visitor_ids_list:
            if visitor_id in existing_visitor_ids:
                continue
            row_blob = visitor_rows.get(visitor_id)
            if row_blob:
                batches["visitor"].append(row_blob)

    print("Prepared rows:")
    for table in sorted(batches.keys()):
        print(f"  {table}: {len(batches[table])}")

    # Insert in FK-safe order.
    insert_rows("user")
    insert_rows("visitor")
    insert_rows("visitor_log")
    insert_rows("shop_sale")
    insert_rows("shop_transaction")
    insert_rows("transaction")
    insert_rows("user_log")

    results = [(table, inserted[table]) for table in sorted(inserted.keys())]
    return results


def _process_insert(
    statement: str,
    table: str,
    columns: dict[str, list[str]],
    date_field: dict[str, str | None],
    batches: dict[str, list[str]],
    existing_ids: dict[str, set[str]],
    visitor_rows: dict[object, str],
    visitor_ids_needed: set[object],
    user_rows: dict[object, str],
    user_ids_needed: set[object],
) -> None:
    prefix = f"INSERT INTO `{table}` VALUES "
    idx = statement.find(prefix)
    if idx == -1:
        return
    values_blob = statement[idx + len(prefix) :].rstrip().rstrip(";")
    rows_blob = _split_rows(values_blob)
    if not rows_blob:
        return
    cols = columns[table]
    col_index = {name: idx for idx, name in enumerate(cols)}
    date_col = date_field.get(table)
    date_idx = cols.index(date_col) if date_col else None
    id_idx = cols.index("id") if "id" in cols else 0

    for row_blob in rows_blob:
        raw_fields = _split_fields(row_blob)
        if len(raw_fields) != len(cols):
            continue
        row_id = _normalize_id(raw_fields[id_idx])
        if table == "user":
            if row_id.upper() == "NULL":
                continue
            user_rows[row_id] = row_blob
            continue
        if table == "visitor":
            if row_id.upper() == "NULL":
                continue
            visitor_rows[row_id] = row_blob
            continue
        if date_idx is not None:
            date_value = raw_fields[date_idx]
            if date_value.startswith("'") and date_value.endswith("'"):
                date_value = date_value[1:-1]
            if not _in_range(date_value):
                continue
        if row_id in existing_ids.get(table, set()):
            continue
        if table == "visitor_log":
            visitor_id = _normalize_id(raw_fields[col_index.get("visitor_id", 2)])
            if visitor_id.upper() != "NULL":
                visitor_ids_needed.add(visitor_id)
            user_id = _normalize_id(raw_fields[col_index.get("user_id", 1)])
            if user_id.upper() != "NULL":
                user_ids_needed.add(user_id)
        if table == "user_log":
            user_id = _normalize_id(raw_fields[col_index.get("user_id", 1)])
            if user_id.upper() != "NULL":
                user_ids_needed.add(user_id)
        batches[table].append(row_blob)


def scan_dump() -> None:
    columns = {table: [] for table in TARGET_TABLES}
    date_field = {
        "visitor_log": "add_time",
        "transaction": "created_at",
        "shop_sale": "created_at",
        "shop_transaction": "created_at",
        "user_log": "start",
        "visitor": None,
    }
    counts = {table: 0 for table in TARGET_TABLES}
    mins = {table: None for table in TARGET_TABLES}
    maxs = {table: None for table in TARGET_TABLES}

    def update_range(table: str, value: str) -> None:
        if not value:
            return
        mins[table] = value if mins[table] is None or value < mins[table] else mins[table]
        maxs[table] = value if maxs[table] is None or value > maxs[table] else maxs[table]

    # We only need column order once, so connect for DESCRIBE.
    conn = connect(TARGET_DB)
    cur = conn.cursor()
    try:
        for table in TARGET_TABLES:
            cur.execute(f"DESCRIBE `{table}`")
            columns[table] = [row[0] for row in cur.fetchall()]
    finally:
        cur.close()
        conn.close()

    in_stmt = False
    buf: list[str] = []
    current_table: str | None = None
    with gzip.open(DUMP_PATH, "rt", encoding="utf-8", errors="ignore") as handle:
        for line in handle:
            stripped = line.lstrip()
            if not in_stmt:
                if stripped.startswith("INSERT INTO `"):
                    in_stmt = True
                    buf = [line]
                    start_idx = stripped.find("`") + 1
                    end_idx = stripped.find("`", start_idx)
                    current_table = (
                        stripped[start_idx:end_idx] if end_idx > start_idx else None
                    )
                    if stripped.rstrip().endswith(";"):
                        in_stmt = False
                        statement = "".join(buf)
                        if current_table in TARGET_TABLES:
                            _scan_statement(
                                statement,
                                current_table,
                                columns,
                                date_field,
                                counts,
                                mins,
                                maxs,
                                update_range,
                            )
                        buf = []
                        current_table = None
                else:
                    continue
            else:
                buf.append(line)
                if stripped.rstrip().endswith(";"):
                    in_stmt = False
                    statement = "".join(buf)
                    if current_table in TARGET_TABLES:
                        _scan_statement(
                            statement,
                            current_table,
                            columns,
                            date_field,
                            counts,
                            mins,
                            maxs,
                            update_range,
                        )
                    buf = []
                    current_table = None

    print("Dump scan results:")
    for table in sorted(counts.keys()):
        print(
            f"  {table}: in-range={counts[table]} min={mins[table]} max={maxs[table]}"
        )


def sample_dump(limit: int = 5) -> None:
    columns = {table: [] for table in TARGET_TABLES}
    date_field = {
        "user": None,
        "visitor_log": "add_time",
        "transaction": "created_at",
        "shop_sale": "created_at",
        "shop_transaction": "created_at",
        "user_log": "start",
        "visitor": None,
    }

    conn = connect(TARGET_DB)
    cur = conn.cursor()
    try:
        for table in TARGET_TABLES:
            cur.execute(f"DESCRIBE `{table}`")
            columns[table] = [row[0] for row in cur.fetchall()]
    finally:
        cur.close()
        conn.close()

    samples: dict[str, list[tuple[str, str]]] = {
        "visitor_log": [],
        "transaction": [],
        "user_log": [],
    }

    in_stmt = False
    buf: list[str] = []
    current_table: str | None = None
    with gzip.open(DUMP_PATH, "rt", encoding="utf-8", errors="ignore") as handle:
        for line in handle:
            stripped = line.lstrip()
            if not in_stmt:
                if stripped.startswith("INSERT INTO `"):
                    in_stmt = True
                    buf = [line]
                    start_idx = stripped.find("`") + 1
                    end_idx = stripped.find("`", start_idx)
                    current_table = (
                        stripped[start_idx:end_idx] if end_idx > start_idx else None
                    )
                    if stripped.rstrip().endswith(";"):
                        in_stmt = False
                        statement = "".join(buf)
                        if current_table in samples:
                            _collect_samples(
                                statement,
                                current_table,
                                columns,
                                date_field,
                                samples,
                                limit,
                            )
                        buf = []
                        current_table = None
                else:
                    continue
            else:
                buf.append(line)
                if stripped.rstrip().endswith(";"):
                    in_stmt = False
                    statement = "".join(buf)
                    if current_table in samples:
                        _collect_samples(
                            statement,
                            current_table,
                            columns,
                            date_field,
                            samples,
                            limit,
                        )
                    buf = []
                    current_table = None
            if all(len(samples[t]) >= limit for t in samples):
                break

    print("Dump samples (id, date):")
    for table in samples:
        print(f"  {table}: {samples[table]}")

    # Check if those ids exist in target DB
    conn = connect(TARGET_DB)
    cur = conn.cursor()
    try:
        for table, rows in samples.items():
            if not rows:
                continue
            ids = [row[0] for row in rows]
            placeholders = ",".join(["%s"] * len(ids))
            date_col = date_field[table]
            cur.execute(
                f"SELECT id, {date_col} FROM `{table}` WHERE id IN ({placeholders})",
                ids,
            )
            print(f"  target {table} rows: {cur.fetchall()}")
    finally:
        cur.close()
        conn.close()


def by_cafe_report() -> None:
    columns = {table: [] for table in TARGET_TABLES}
    date_field = {
        "user": None,
        "visitor_log": "add_time",
        "transaction": "created_at",
        "shop_sale": "created_at",
        "shop_transaction": "created_at",
        "user_log": "start",
        "visitor": None,
    }
    cafe_field = {
        "visitor_log": "cafe_id",
        "transaction": "cafe_id",
        "shop_sale": "cafe_id",
        "shop_transaction": "cafe_id",
        "user_log": "cafe_id",
    }

    conn = connect(TARGET_DB)
    cur = conn.cursor()
    try:
        for table in TARGET_TABLES:
            cur.execute(f"DESCRIBE `{table}`")
            columns[table] = [row[0] for row in cur.fetchall()]
    finally:
        cur.close()
        conn.close()

    dump_counts: dict[str, dict[str, int]] = {
        table: {} for table in cafe_field.keys()
    }

    in_stmt = False
    buf: list[str] = []
    current_table: str | None = None
    with gzip.open(DUMP_PATH, "rt", encoding="utf-8", errors="ignore") as handle:
        for line in handle:
            stripped = line.lstrip()
            if not in_stmt:
                if stripped.startswith("INSERT INTO `"):
                    in_stmt = True
                    buf = [line]
                    start_idx = stripped.find("`") + 1
                    end_idx = stripped.find("`", start_idx)
                    current_table = (
                        stripped[start_idx:end_idx] if end_idx > start_idx else None
                    )
                    if stripped.rstrip().endswith(";"):
                        in_stmt = False
                        statement = "".join(buf)
                        if current_table in dump_counts:
                            _count_by_cafe_statement(
                                statement,
                                current_table,
                                columns,
                                date_field,
                                cafe_field,
                                dump_counts,
                            )
                        buf = []
                        current_table = None
                else:
                    continue
            else:
                buf.append(line)
                if stripped.rstrip().endswith(";"):
                    in_stmt = False
                    statement = "".join(buf)
                    if current_table in dump_counts:
                        _count_by_cafe_statement(
                            statement,
                            current_table,
                            columns,
                            date_field,
                            cafe_field,
                            dump_counts,
                        )
                    buf = []
                    current_table = None

    print("Dump counts by cafe in range:")
    for table in sorted(dump_counts.keys()):
        print(f"  {table}: {dump_counts[table]}")

    print("Target DB counts by cafe in range:")
    conn = connect(TARGET_DB)
    cur = conn.cursor()
    try:
        lt = "<"
        for table, cafe_col in cafe_field.items():
            date_col = date_field[table]
            cur.execute(
                f"SELECT {cafe_col}, COUNT(*) FROM `{table}` "
                f"WHERE {date_col} >= '{DATE_START}' AND {date_col} {lt} '{DATE_END}' "
                f"GROUP BY {cafe_col} ORDER BY {cafe_col}"
            )
            print(f"  {table}: {cur.fetchall()}")
    finally:
        cur.close()
        conn.close()


def _count_by_cafe_statement(
    statement: str,
    table: str,
    columns: dict[str, list[str]],
    date_field: dict[str, str | None],
    cafe_field: dict[str, str],
    dump_counts: dict[str, dict[str, int]],
) -> None:
    prefix = f"INSERT INTO `{table}` VALUES "
    idx = statement.find(prefix)
    if idx == -1:
        return
    values_blob = statement[idx + len(prefix) :].rstrip().rstrip(";")
    rows_blob = _split_rows(values_blob)
    if not rows_blob:
        return
    cols = columns[table]
    col_index = {name: idx for idx, name in enumerate(cols)}
    date_idx = col_index.get(date_field[table])
    cafe_idx = col_index.get(cafe_field[table])
    if date_idx is None or cafe_idx is None:
        return
    for row_blob in rows_blob:
        raw_fields = _split_fields(row_blob)
        if len(raw_fields) != len(cols):
            continue
        date_value = raw_fields[date_idx]
        if date_value.startswith("'") and date_value.endswith("'"):
            date_value = date_value[1:-1]
        if not _in_range(date_value):
            continue
        cafe_value = _normalize_id(raw_fields[cafe_idx])
        dump_counts[table][cafe_value] = dump_counts[table].get(cafe_value, 0) + 1


def _collect_samples(
    statement: str,
    table: str,
    columns: dict[str, list[str]],
    date_field: dict[str, str | None],
    samples: dict[str, list[tuple[str, str]]],
    limit: int,
) -> None:
    if len(samples.get(table, [])) >= limit:
        return
    prefix = f"INSERT INTO `{table}` VALUES "
    idx = statement.find(prefix)
    if idx == -1:
        return
    values_blob = statement[idx + len(prefix) :].rstrip().rstrip(";")
    rows_blob = _split_rows(values_blob)
    if not rows_blob:
        return
    cols = columns[table]
    col_index = {name: idx for idx, name in enumerate(cols)}
    date_col = date_field.get(table)
    date_idx = col_index.get(date_col) if date_col else None
    id_idx = col_index.get("id", 0)
    for row_blob in rows_blob:
        if len(samples[table]) >= limit:
            break
        raw_fields = _split_fields(row_blob)
        if len(raw_fields) != len(cols):
            continue
        row_id = _normalize_id(raw_fields[id_idx])
        date_value = raw_fields[date_idx] if date_idx is not None else ""
        if date_value.startswith("'") and date_value.endswith("'"):
            date_value = date_value[1:-1]
        if date_idx is not None and not _in_range(date_value):
            continue
        samples[table].append((row_id, date_value))

def _scan_statement(
    statement: str,
    table: str,
    columns: dict[str, list[str]],
    date_field: dict[str, str | None],
    counts: dict[str, int],
    mins: dict[str, str | None],
    maxs: dict[str, str | None],
    update_range,
) -> None:
    prefix = f"INSERT INTO `{table}` VALUES "
    idx = statement.find(prefix)
    if idx == -1:
        return
    values_blob = statement[idx + len(prefix) :].rstrip().rstrip(";")
    rows_blob = _split_rows(values_blob)
    if not rows_blob:
        return
    cols = columns[table]
    date_col = date_field.get(table)
    date_idx = cols.index(date_col) if date_col else None
    for row_blob in rows_blob:
        raw_fields = _split_fields(row_blob)
        if len(raw_fields) != len(cols):
            continue
        if date_idx is None:
            continue
        date_value = raw_fields[date_idx]
        if date_value.startswith("'") and date_value.endswith("'"):
            date_value = date_value[1:-1]
        update_range(table, date_value)
        if _in_range(date_value):
            counts[table] += 1


def main() -> None:
    print("Connecting to MySQL...")
    conn = connect()
    cur = conn.cursor()
    try:
        results = insert_range(cur)
        print("Insert results:")
        for name, count in results:
            print(f"  {name}: inserted {count}")
    finally:
        cur.close()
        conn.close()


if __name__ == "__main__":
    if "--scan" in sys.argv:
        scan_dump()
    elif "--sample" in sys.argv:
        sample_dump()
    elif "--by-cafe" in sys.argv:
        by_cafe_report()
    elif "--count" in sys.argv:
        conn = connect(TARGET_DB)
        cur = conn.cursor()
        try:
            print("Target DB counts:")
            lt = "<"
            cur.execute(
                f"SELECT COUNT(*), MIN(add_time), MAX(add_time) FROM `visitor_log` "
                f"WHERE add_time >= '{DATE_START}' AND add_time {lt} '{DATE_END}'"
            )
            count, min_dt, max_dt = cur.fetchone()
            print(f"  visitor_log: {count} (min={min_dt}, max={max_dt})")
            cur.execute(
                f"SELECT COUNT(*), MIN(created_at), MAX(created_at) FROM `transaction` "
                f"WHERE created_at >= '{DATE_START}' AND created_at {lt} '{DATE_END}'"
            )
            count, min_dt, max_dt = cur.fetchone()
            print(f"  transaction: {count} (min={min_dt}, max={max_dt})")
            cur.execute(
                f"SELECT COUNT(*), MIN(created_at), MAX(created_at) FROM `shop_sale` "
                f"WHERE created_at >= '{DATE_START}' AND created_at {lt} '{DATE_END}'"
            )
            count, min_dt, max_dt = cur.fetchone()
            print(f"  shop_sale: {count} (min={min_dt}, max={max_dt})")
            cur.execute(
                f"SELECT COUNT(*), MIN(created_at), MAX(created_at) FROM `shop_transaction` "
                f"WHERE created_at >= '{DATE_START}' AND created_at {lt} '{DATE_END}'"
            )
            count, min_dt, max_dt = cur.fetchone()
            print(f"  shop_transaction: {count} (min={min_dt}, max={max_dt})")
            cur.execute(
                f"SELECT COUNT(*), MIN(start), MAX(start) FROM `user_log` "
                f"WHERE start >= '{DATE_START}' AND start {lt} '{DATE_END}'"
            )
            count, min_dt, max_dt = cur.fetchone()
            print(f"  user_log: {count} (min={min_dt}, max={max_dt})")
        finally:
            cur.close()
            conn.close()
    else:
        main()
