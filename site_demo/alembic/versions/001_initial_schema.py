"""Initial schema for Kavork cafe management.

Revision ID: 001_initial
Revises:
Create Date: 2026-01-16

"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op

# revision identifiers, used by Alembic.
revision: str = "001_initial"
down_revision: Union[str, None] = None
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    # Create franchisee table
    op.create_table(
        "franchisee",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("name", sa.String(length=255), nullable=False),
        sa.Column("code", sa.String(length=50), nullable=False),
        sa.Column("max_cafe", sa.Integer(), nullable=False, server_default="1"),
        sa.Column("roles", sa.Text(), nullable=True),
        sa.Column("is_active", sa.Boolean(), nullable=False, server_default="true"),
        sa.Column("created_at", sa.DateTime(), nullable=False, server_default=sa.text("now()")),
        sa.PrimaryKeyConstraint("id"),
        sa.UniqueConstraint("code"),
    )

    # Create cafe table
    op.create_table(
        "cafe",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("franchisee_id", sa.Integer(), nullable=False),
        sa.Column("name", sa.String(length=255), nullable=False),
        sa.Column("address", sa.Text(), nullable=True),
        sa.Column("max_person", sa.Integer(), nullable=False, server_default="0"),
        sa.Column("currency", sa.String(length=10), nullable=False, server_default="'USD'"),
        sa.Column("logo", sa.String(length=255), nullable=True),
        sa.Column("api_key", sa.String(length=64), nullable=True),
        sa.Column("child_discount", sa.Integer(), nullable=False, server_default="0"),
        sa.Column("selfservice_timeout", sa.Integer(), nullable=False, server_default="5"),
        sa.Column("tips_var", sa.String(length=100), nullable=True),
        sa.Column("is_active", sa.Boolean(), nullable=False, server_default="true"),
        sa.Column("init_successful", sa.Boolean(), nullable=False, server_default="false"),
        sa.Column("created_at", sa.DateTime(), nullable=False, server_default=sa.text("now()")),
        sa.ForeignKeyConstraint(["franchisee_id"], ["franchisee.id"]),
        sa.PrimaryKeyConstraint("id"),
    )

    # Create user table
    op.create_table(
        "user",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("franchisee_id", sa.Integer(), nullable=False),
        sa.Column("name", sa.String(length=255), nullable=False),
        sa.Column("email", sa.String(length=255), nullable=True),
        sa.Column("pass_hash", sa.String(length=255), nullable=False),
        sa.Column("phone", sa.String(length=20), nullable=True),
        sa.Column("color", sa.String(length=20), nullable=True),
        sa.Column("language", sa.String(length=5), nullable=False, server_default="'en'"),
        sa.Column("state", sa.Integer(), nullable=False, server_default="0"),
        sa.Column("last_session", sa.String(length=255), nullable=True),
        sa.Column("last_login_at", sa.DateTime(), nullable=True),
        sa.Column("created_at", sa.DateTime(), nullable=False, server_default=sa.text("now()")),
        sa.ForeignKeyConstraint(["franchisee_id"], ["franchisee.id"]),
        sa.PrimaryKeyConstraint("id"),
        sa.UniqueConstraint("email"),
        sa.UniqueConstraint("name"),
    )

    # Create visitor table
    op.create_table(
        "visitor",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("franchisee_id", sa.Integer(), nullable=False),
        sa.Column("code", sa.String(length=50), nullable=False),
        sa.Column("first_name", sa.String(length=255), nullable=False),
        sa.Column("last_name", sa.String(length=255), nullable=True),
        sa.Column("email", sa.String(length=255), nullable=True),
        sa.Column("phone", sa.String(length=20), nullable=True),
        sa.Column("notice", sa.Text(), nullable=True),
        sa.Column("language", sa.String(length=5), nullable=False, server_default="'en'"),
        sa.Column("visit_count", sa.Integer(), nullable=False, server_default="0"),
        sa.Column("created_at", sa.DateTime(), nullable=False, server_default=sa.text("now()")),
        sa.ForeignKeyConstraint(["franchisee_id"], ["franchisee.id"]),
        sa.PrimaryKeyConstraint("id"),
        sa.UniqueConstraint("code"),
        sa.UniqueConstraint("phone"),
    )


def downgrade() -> None:
    op.drop_table("visitor")
    op.drop_table("user")
    op.drop_table("cafe")
    op.drop_table("franchisee")
