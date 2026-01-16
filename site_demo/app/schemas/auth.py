from datetime import datetime
from typing import Optional

from pydantic import BaseModel, EmailStr


class UserBase(BaseModel):
    """Base user schema for cafe staff."""

    id: int
    name: str
    email: Optional[EmailStr] = None
    franchisee_id: int
    is_active: bool = True
    last_login_at: Optional[datetime] = None


class AuthTokens(BaseModel):
    """JWT authentication tokens."""

    access_token: str
    refresh_token: str
    token_type: str = "bearer"
    expires_in: int


class AuthResponse(BaseModel):
    """Response after successful authentication."""

    user: UserBase
    tokens: AuthTokens


class AuthMeResponse(UserBase):
    """Current user profile response."""

    @staticmethod
    def mock() -> "AuthMeResponse":
        return AuthMeResponse(
            id=0,
            name="demo_user",
            email="demo@example.com",
            franchisee_id=1,
            is_active=True,
            last_login_at=None,
        )


