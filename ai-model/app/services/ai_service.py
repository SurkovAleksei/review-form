import json
import logging
from typing import Dict, Any
from ..config import Config
from .fallback import FallbackService

from gigachat import GigaChat
from gigachat.models import Chat, Messages, MessagesRole

logger = logging.getLogger(__name__)

class AIService:
    def __init__(self):
        self.client = None
        self.model = Config.GIGACHAT_MODEL
        self.fallback = FallbackService()
        self.ai_available = False
        
        if Config.GIGACHAT_CREDENTIALS:
            try:
                self.client = GigaChat(
                    credentials=Config.GIGACHAT_CREDENTIALS,
                    scope=Config.GIGACHAT_SCOPE,
                    verify_ssl_certs=Config.GIGACHAT_VERIFY_SSL_CERTS
                )
                self.ai_available = True
                logger.info("GigaChat client initialized successfully")
            except Exception as e:
                logger.error(f"Failed to initialize GigaChat: {e}")
                self.ai_available = False
        else:
            logger.warning("GigaChat credentials not set")
    
    def analyze(self, name: str, email: str, comment: str) -> Dict[str, Any]:
        if not self.ai_available or not self.client:
            logger.warning("AI not available, using fallback")
            return self.fallback.analyze(name, comment)
        
        try:
            messages = [
                Messages(
                    role=MessagesRole.SYSTEM,
                    content="Ты профессиональный ассистент. Отвечай только JSON."
                ),
                Messages(
                    role=MessagesRole.USER,
                    content=self._build_prompt(name, comment)
                )
            ]
            
            payload = Chat(
                model=self.model,
                messages=messages,
                temperature=0.7,
                max_tokens=600,
            )
            
            response = self.client.chat(payload)
            
            content = response.choices[0].message.content
            logger.info(f"GigaChat response received")
            
            try:
                data = json.loads(content)
            except json.JSONDecodeError:
                import re
                match = re.search(r'\{.*\}', content, re.DOTALL)
                if match:
                    data = json.loads(match.group())
                else:
                    data = {}
            
            return {
                'category': data.get('category', 'other'),
                'sentiment': data.get('sentiment', 'neutral'),
                'sentiment_score': max(0, min(int(data.get('sentiment_score', 5)), 10)),
                'urgency': max(1, min(int(data.get('urgency', 3)), 5)),
                'auto_reply': data.get('auto_reply', self.fallback._default_reply(name)),
                'key_topics': data.get('key_topics', [])[:4] if isinstance(data.get('key_topics'), list) else [],
                'ai_used': True,
                'fallback': False
            }
            
        except Exception as e:
            logger.error(f"GigaChat error: {e}")
            return self.fallback.analyze(name, comment)
    
    def _build_prompt(self, name: str, comment: str) -> str:
        return f"""
Проанализируй обращение от пользователя:

Имя: {name}
Сообщение: {comment}

Верни ТОЛЬКО JSON:
{{
    "category": "question|proposal|complaint|other",
    "sentiment": "positive|neutral|negative",
    "sentiment_score": 5,
    "urgency": 3,
    "auto_reply": "персонализированный ответ на русском",
    "key_topics": ["тема1", "тема2"]
}}
"""