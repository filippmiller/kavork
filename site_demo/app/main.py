from fastapi import FastAPI

from .config import get_settings
from .routes import auth as auth_routes

settings = get_settings()

app = FastAPI(
    title=settings.app_name,
    version="0.1.0",
    docs_url="/docs",
    redoc_url="/redoc",
)

app.include_router(auth_routes.router)


@app.get("/healthz", tags=["health"])
def health_check() -> dict[str, str]:
    return {"status": "ok"}


