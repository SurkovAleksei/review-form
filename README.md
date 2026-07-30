API для формы обратной связи с AI-анализом (GigaChat)

## Описание проекта
это бэкенд-сервис для формы обратной связи Сервис включает:
- **Прием и валидацию** данных формы
- **AI-анализ** текста через GigaChat (тональность, категория, генерация ответа)
- **Email уведомления** владельцу сайта и пользователю
- **Защиту от спама** (Rate Limiting — 5 запросов в час)
- **Логирование** всех запросов в файл
- **Swagger** документацию
  
## Стек технологий
**Backend** Laravel 11, PHP 8.4
**AI Service** Flask, Python 3.11, GigaChat API
**Frontend** Vue.js 3, HTML5, CSS3
**Email** SMTP (Gmail)
**Документация** Swagger
**Контейнеризация** Docker, Docker Compose
**Web Server** Nginx 

## ДОСТУПНЫЕ URL
http://localhost:8080/index.html	Форма обратной связи (фронтенд)
http://localhost:8080/api/health	Проверка статуса API
http://localhost:8080/api/contact	Отправка формы	POST
http://localhost:8080/api/documentation	Swagger документация
http://localhost:8000/api/health	Проверка статуса API (прямой доступ)
http://localhost:8000/api/contact	Отправка формы (прямой доступ)
http://localhost:5000/health	Проверка статуса AI сервиса
http://localhost:5000/analyze	AI анализ текста (Flask)

## Для запуска
1. Клонирование репозитория
bash
git clone https://github.com/SurkovAleksei/review-form.git
cd review-form

2. Запуск через Docker 
bash
# Сборка и запуск всех контейнеров
docker-compose up -d --build

# Проверка статуса контейнеров
docker-compose ps

3. Настройка .env файлов
bash
# Создайте .env файлы из примеров
cp backend/.env.example backend/.env
cp ai-model/.env.example ai-model/.env

1. Заполните .env:
Ключ от gigachat
GIGACHAT_CREDENTIALS=your-credentials 

Почту для отправки и получения
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=your-email@gmail.com
OWNER_EMAIL=your-email@gmail.com

