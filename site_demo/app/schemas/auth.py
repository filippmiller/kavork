from datetime import datetime
from typing import Optional

from pydantic import BaseModel, EmailStr


class UserBase(BaseModel):
    id: str
    email: EmailStr
    role: str
    is_active: bool
    email_verified: bool
    last_login_at: Optional[datetime] = None


class AuthTokens(BaseModel):
    access_token: str
    refresh_token: str
    token_type: str = "bearer"
    expires_in: int


class AuthResponse(BaseModel):
    user: UserBase
    tokens: AuthTokens


class AuthMeResponse(UserBase):
    @staticmethod
    def mock() -> "AuthMeResponse":
        return AuthMeResponse(
            id="00000000-0000-0000-0000-000000000000",
            email="placeholder@example.com",
            role="owner",
            is_active=True,
            email_verified=False,
            last_login_at=None,
        )


