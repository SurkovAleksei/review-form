FROM nginx:alpine

COPY .docker/nginx/nginx.conf /etc/nginx/nginx.conf

COPY frontend /var/www/frontend

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]