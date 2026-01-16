from datetime import datetime
from typing import TYPE_CHECKING

from sqlalchemy import Boolean, DateTime, ForeignKey, Integer, String, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship

from ..db import Base

if TYPE_CHECKING:
    from .franchisee import Franchisee


class Cafe(Base):
    """Cafe/anticafe location."""

    __tablename__ = "cafe"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    franchisee_id: Mapped[int] = mapped_column(Integer, ForeignKey("franchisee.id"), nullable=False)
    name: Mapped[str] = mapped_column(String(255), nullable=False)
    address: Mapped[str | None] = mapped_column(Text, nullable=True)
    max_person: Mapped[int] = mapped_column(Integer, default=0)
    currency: Mapped[str] = mapped_column(String(10), default="USD")
    logo: Mapped[str | None] = mapped_column(String(255), nullable=True)
    api_key: Mapped[str | None] = mapped_column(String(64), nullable=True)
    child_discount: Mapped[int] = mapped_column(Integer, default=0)
    selfservice_timeout: Mapped[int] = mapped_column(Integer, default=5)
    tips_var: Mapped[str | None] = mapped_column(String(100), nullable=True)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    init_successful: Mapped[bool] = mapped_column(Boolean, default=False)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)

    # Relationships
    franchisee: Mapped["Franchisee"] = relationship("Franchisee", back_populates="cafes")

    def __repr__(self) -> str:
        return f"<Cafe(id={self.id}, name='{self.name}')>"
