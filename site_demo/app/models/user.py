from sqlalchemy import Integer, Text
from sqlalchemy.orm import Mapped, mapped_column

from ..db import Base


class User(Base):
    """Staff/admin user for cafe management (matches existing MySQL schema)."""

    __tablename__ = "user"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    user: Mapped[str | None] = mapped_column(Text, nullable=True)  # username/login
    pass_: Mapped[str | None] = mapped_column("pass", Text, nullable=True)  # password hash
    last_sess: Mapped[str | None] = mapped_column(Text, nullable=True)
    role: Mapped[int | None] = mapped_column(Integer, nullable=True)
    state: Mapped[int | None] = mapped_column(Integer, nullable=True)
    cafe: Mapped[str | None] = mapped_column(Text, nullable=True)
    email: Mapped[str | None] = mapped_column(Text, nullable=True)
    color: Mapped[str | None] = mapped_column(Text, nullable=True)

    def __repr__(self) -> str:
        return f"<User(id={self.id}, user='{self.user}', email='{self.email}')>"
