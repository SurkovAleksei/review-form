from typing import List, Optional
from pydantic import BaseModel

class AnalyzeRequest(BaseModel):
    name: str
    email: str
    comment: str

class AnalyzeResponse(BaseModel):
    category: str
    sentiment: str
    sentiment_score: int
    urgency: int
    auto_reply: str
    key_topics: List[str]
    ai_used: bool = True
    fallback: bool = False

class ErrorResponse(BaseModel):
    success: bool
    error: str