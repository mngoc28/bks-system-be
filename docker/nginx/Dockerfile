FROM nginx:1.26.1

WORKDIR /var/www

COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]