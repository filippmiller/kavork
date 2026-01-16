from sqlalchemy import Integer, Text
from sqlalchemy.orm import Mapped, mapped_column

from ..db import Base


class Cafe(Base):
    """Cafe/anticafe location (matches existing MySQL schema)."""

    __tablename__ = "cafe"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    name: Mapped[str | None] = mapped_column(Text, nullable=True)
    max_person: Mapped[int | None] = mapped_column(Integer, nullable=True)
    adres_1: Mapped[str | None] = mapped_column(Text, nullable=True)
    adres_2: Mapped[str | None] = mapped_column(Text, nullable=True)
    tps: Mapped[str | None] = mapped_column(Text, nullable=True)
    tvq: Mapped[str | None] = mapped_column(Text, nullable=True)
    last_task: Mapped[int | None] = mapped_column(Integer, nullable=True)

    def __repr__(self) -> str:
        return f"<Cafe(id={self.id}, name='{self.name}')>"
