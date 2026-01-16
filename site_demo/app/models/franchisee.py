from datetime import datetime
from typing import TYPE_CHECKING

from sqlalchemy import Boolean, DateTime, Integer, String, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship

from ..db import Base

if TYPE_CHECKING:
    from .cafe import Cafe
    from .user import User
    from .visitor import Visitor


class Franchisee(Base):
    """Franchise organization that owns multiple cafes."""

    __tablename__ = "franchisee"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(255), nullable=False)
    code: Mapped[str] = mapped_column(String(50), nullable=False, unique=True)
    max_cafe: Mapped[int] = mapped_column(Integer, default=1)
    roles: Mapped[str | None] = mapped_column(Text, nullable=True)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)

    # Relationships
    cafes: Mapped[list["Cafe"]] = relationship("Cafe", back_populates="franchisee")
    users: Mapped[list["User"]] = relationship("User", back_populates="franchisee")
    visitors: Mapped[list["Visitor"]] = relationship("Visitor", back_populates="franchisee")

    def __repr__(self) -> str:
        return f"<Franchisee(id={self.id}, name='{self.name}', code='{self.code}')>"
