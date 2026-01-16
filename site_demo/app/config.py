from functools import lru_cache
from typing import Optional

from pydantic import Field
from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    """Centralized application configuration for Kavork Cafe Management."""

    app_name: str = "Kavork Cafe Management API"
    environment: str = Field(default="development", pattern="^(development|staging|production)$")

    # MySQL connection settings
    mysql_host: str = Field(alias="MYSQLHOST", default="localhost")
    mysql_port: int = Field(alias="MYSQLPORT", default=3306)
    mysql_user: str = Field(alias="MYSQLUSER", default="root")
    mysql_password: str = Field(alias="MYSQLPASSWORD", default="")
    mysql_database: str = Field(alias="MYSQLDATABASE", default="railway")

    jwt_secret: str = Field(alias="JWT_SECRET", min_length=32)
    jwt_algorithm: str = "HS256"
    access_token_exp_minutes: int = 20
    refresh_token_ttl_days: int = 30

    encryption_key: Optional[str] = Field(alias="ENCRYPTION_KEY", default=None)

    @property
    def database_url(self) -> str:
        """Build MySQL connection URL for SQLAlchemy."""
        return f"mysql+pymysql://{self.mysql_user}:{self.mysql_password}@{self.mysql_host}:{self.mysql_port}/{self.mysql_database}"

    class Config:
        env_file = ".env"
        case_sensitive = False


@lru_cache
def get_settings() -> Settings:
    return Settings()  # type: ignore[call-arg]


