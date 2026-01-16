from sqlalchemy import Integer, Text
from sqlalchemy.orm import Mapped, mapped_column

from ..db import Base


class Visitor(Base):
    """Customer/visitor of the cafe (matches existing MySQL schema)."""

    __tablename__ = "visitor"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    f_name: Mapped[str | None] = mapped_column(Text, nullable=True)
    l_name: Mapped[str | None] = mapped_column(Text, nullable=True)
    code: Mapped[str | None] = mapped_column(Text, nullable=True)
    email: Mapped[str | None] = mapped_column(Text, nullable=True)
    phone: Mapped[str | None] = mapped_column(Text, nullable=True)
    creat: Mapped[int | None] = mapped_column(Integer, nullable=True)  # timestamp
    notice: Mapped[str | None] = mapped_column(Text, nullable=True)
    lang: Mapped[int | None] = mapped_column(Integer, nullable=True)

    @property
    def full_name(self) -> str:
        parts = []
        if self.f_name:
            parts.append(self.f_name)
        if self.l_name:
            parts.append(self.l_name)
        return " ".join(parts)

    def __repr__(self) -> str:
        return f"<Visitor(id={self.id}, code='{self.code}', name='{self.full_name}')>"
