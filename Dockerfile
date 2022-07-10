FROM php:8.1

RUN rm -Rf /var/www/html
RUN mkdir /var/www/app

COPY . /var/www/app

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

RUN apt-get update
RUN apt-get install -y libzip-dev
RUN docker-php-ext-install zip pdo pdo_mysql

WORKDIR /var/www/app

RUN mkdir keys

WORKDIR /var/www/app/keys

RUN openssl genpkey -algorithm RSA -out private.pem -pkeyopt rsa_keygen_bits:2048
RUN openssl rsa -pubout -in private.pem -out public.pem

WORKDIR /var/www/app

RUN composer install

ENTRYPOINT ["/bin/sh", "-c" , "bin/console doctrine:database:create --if-not-exists && bin/console doctrine:migrations:migrate --no-interaction && cd public && php -S 0.0.0.0:8080"]