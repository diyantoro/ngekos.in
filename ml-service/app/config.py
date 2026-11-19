import os

try:
    from dotenv import load_dotenv
    load_dotenv()
except ImportError:  # python-dotenv opsional saat dijalankan tanpa container
    pass


class Settings:
    SERVICE_NAME: str = "ngekos-ml-service"
    SERVICE_VERSION: str = "1.0.0"
    API_TOKEN: str = os.getenv("ML_SERVICE_TOKEN", "ngekos-secret-api-token")
    MODEL_VERSION: str = os.getenv("MODEL_VERSION", "v1.0-mlp-baseline")
    BASE_DIR: str = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    ARTIFACTS_DIR: str = os.path.join(BASE_DIR, "artifacts")


settings = Settings()
os.makedirs(settings.ARTIFACTS_DIR, exist_ok=True)
