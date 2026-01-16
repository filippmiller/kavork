from typing import Optional

from pydantic import BaseModel, EmailStr


class UserBase(BaseModel):
    """Base user schema for cafe staff (matches MySQL schema)."""

    id: int
    user: str  # username/login
    email: Optional[EmailStr] = None
    role: Optional[int] = None
    state: Optional[int] = None  # 0=active


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
            user="demo_user",
            email="demo@example.com",
            role=0,
            state=0,
        )


