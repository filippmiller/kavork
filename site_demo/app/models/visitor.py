from datetime import datetime
from typing import TYPE_CHECKING

from sqlalchemy import DateTime, ForeignKey, Integer, String, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship

from ..db import Base

if TYPE_CHECKING:
    from .franchisee import Franchisee


class Visitor(Base):
    """Customer/visitor of the cafe."""

    __tablename__ = "visitor"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    franchisee_id: Mapped[int] = mapped_column(Integer, ForeignKey("franchisee.id"), nullable=False)
    code: Mapped[str] = mapped_column(String(50), nullable=False, unique=True)
    first_name: Mapped[str] = mapped_column(String(255), nullable=False)
    last_name: Mapped[str | None] = mapped_column(String(255), nullable=True)
    email: Mapped[str | None] = mapped_column(String(255), nullable=True)
    phone: Mapped[str | None] = mapped_column(String(20), nullable=True, unique=True)
    notice: Mapped[str | None] = mapped_column(Text, nullable=True)
    language: Mapped[str] = mapped_column(String(5), default="en")
    visit_count: Mapped[int] = mapped_column(Integer, default=0)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)

    # Relationships
    franchisee: Mapped["Franchisee"] = relationship("Franchisee", back_populates="visitors")

    @property
    def full_name(self) -> str:
        parts = [self.first_name]
        if self.last_name:
            parts.append(self.last_name)
        return " ".join(parts)

    def __repr__(self) -> str:
        return f"<Visitor(id={self.id}, code='{self.code}', name='{self.full_name}')>"
