class FallbackService:
    def analyze(self, name: str, comment: str) -> dict:
        comment_lower = comment.lower()
        
        categories = {
            'question': ['вопрос', 'как', 'что', 'почему', 'где', 'когда'],
            'proposal': ['предлож', 'хочу', 'идея', 'заказать', 'сделать'],
            'complaint': ['проблем', 'ошибк', 'не работ', 'слом', 'баг'],
        }
        
        category = 'other'
        for cat, keywords in categories.items():
            if any(w in comment_lower for w in keywords):
                category = cat
                break
        
        positive = ['спасиб', 'отличн', 'хорош', 'нравит', 'классн', 'супер']
        negative = ['плох', 'ужасн', 'не нравит', 'разочар', 'проблем', 'ошибк']
        
        pos_score = sum(1 for w in positive if w in comment_lower)
        neg_score = sum(1 for w in negative if w in comment_lower)
        
        if pos_score > neg_score:
            sentiment = 'positive'
            score = min(7 + (pos_score - neg_score), 10)
        elif neg_score > pos_score:
            sentiment = 'negative'
            score = max(3 - (neg_score - pos_score), 0)
        else:
            sentiment = 'neutral'
            score = 5
        
        urgency = 3
        if any(w in comment_lower for w in ['срочн', 'быстр', 'сегодня']):
            urgency = 5
        elif any(w in comment_lower for w in ['скоро', 'завтра']):
            urgency = 4
        
        topics = []
        topic_keywords = {
            'сайт': ['сайт', 'лендинг', 'интернет-магазин'],
            'дизайн': ['дизайн', 'верстка', 'макет'],
            'разработка': ['разработка', 'программирование', 'код'],
            'оптимизация': ['скорость', 'оптимизация', 'производительность'],
        }
        
        for topic, keywords in topic_keywords.items():
            if any(w in comment_lower for w in keywords):
                topics.append(topic)
        
        return {
            'category': category,
            'sentiment': sentiment,
            'sentiment_score': score,
            'urgency': urgency,
            'auto_reply': self._default_reply(name),  # 👈 Вызываем метод
            'key_topics': topics[:4],
            'ai_used': False,
            'fallback': True
        }
    
    def _default_reply(self, name: str) -> str:  # 👈 Добавляем метод
        greeting = f"Здравствуйте, {name}!" if name else "Здравствуйте!"
        return f"""{greeting}

Спасибо за ваше обращение. Я внимательно изучил ваш запрос и свяжусь с вами в ближайшее время.

Если у вас есть дополнительные вопросы, не стесняйтесь задавать их.

С уважением,
Разработчик"""