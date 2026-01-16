from functools import lru_cache

from pydantic import AnyUrl, Field
from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    """Centralized application configuration for Kavork Cafe Management."""

    app_name: str = "Kavork Cafe Management API"
    environment: str = Field(default="development", pattern="^(development|staging|production)$")

    database_url: AnyUrl = Field(alias="DATABASE_URL")

    jwt_secret: str = Field(alias="JWT_SECRET", min_length=32)
    jwt_algorithm: str = "HS256"
    access_token_exp_minutes: int = 20
    refresh_token_ttl_days: int = 30

    encryption_key: str = Field(alias="ENCRYPTION_KEY", min_length=32)

    class Config:
        env_file = ".env"
        case_sensitive = False


@lru_cache
def get_settings() -> Settings:
    return Settings()  # type: ignore[call-arg]


