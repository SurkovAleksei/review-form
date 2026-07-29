FROM python:3.11-slim

WORKDIR /var/www/ai-model

COPY ai-model/ /var/www/ai-model

RUN pip install --no-cache-dir -r requirements.txt

EXPOSE 5000

CMD ["python", "run.py"]