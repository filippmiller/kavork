from fastapi import APIRouter, Depends, status

from ..schemas.auth import AuthMeResponse

router = APIRouter(prefix="/auth", tags=["auth"])


@router.get("/me", response_model=AuthMeResponse, status_code=status.HTTP_200_OK)
def read_current_user(current_user: AuthMeResponse = Depends(lambda: AuthMeResponse.mock())) -> AuthMeResponse:
    """
    Placeholder endpoint that will be replaced with real authentication logic.
    """
    return current_user


