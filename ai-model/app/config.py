import os
from dotenv import load_dotenv

load_dotenv()

class Config:
    GIGACHAT_CREDENTIALS = os.getenv('GIGACHAT_CREDENTIALS')
    GIGACHAT_SCOPE = os.getenv('GIGACHAT_SCOPE', 'GIGACHAT_API_PERS')
    GIGACHAT_MODEL = os.getenv('GIGACHAT_MODEL', 'GigaChat-2-Pro')
    GIGACHAT_VERIFY_SSL_CERTS = os.getenv('GIGACHAT_VERIFY_SSL_CERTS', 'false').lower() == 'true'
    
    FLASK_DEBUG = os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    FLASK_PORT = int(os.getenv('FLASK_PORT', 5000))
    
    @classmethod
    def validate(cls):
        if not cls.GIGACHAT_CREDENTIALS:
            print("⚠️  WARNING: GIGACHAT_CREDENTIALS not set in .env")
            return False
        return True