from functools import lru_cache
from typing import Optional

from pydantic import AnyUrl, Field
from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    """Centralized application configuration."""

    app_name: str = "NEW BIDNAPPER API"
    environment: str = Field(default="development", pattern="^(development|staging|production)$")

    database_url: AnyUrl = Field(alias="DATABASE_URL")

    jwt_secret: str = Field(alias="JWT_SECRET", min_length=32)
    jwt_algorithm: str = "HS256"
    access_token_exp_minutes: int = 20
    refresh_token_ttl_days: int = 30

    encryption_key: str = Field(alias="ENCRYPTION_KEY", min_length=32)

    ebay_client_id: Optional[str] = Field(default=None, alias="EBAY_CLIENT_ID")
    ebay_client_secret: Optional[str] = Field(default=None, alias="EBAY_CLIENT_SECRET")
    ebay_redirect_uri: Optional[AnyUrl] = Field(default=None, alias="EBAY_REDIRECT_URI")
    ebay_environment: str = Field(default="prod", alias="EBAY_ENVIRONMENT")
    ebay_scopes: list[str] = Field(
        default_factory=lambda: [
            "https://api.ebay.com/oauth/api_scope",
            "https://api.ebay.com/oauth/api_scope/buy.order.readonly",
        ]
    )

    security_event_page_size: int = 100

    class Config:
        env_file = ".env"
        case_sensitive = False


@lru_cache
def get_settings() -> Settings:
    return Settings()  # type: ignore[call-arg]


