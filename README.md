# AMS.Connect
## Upgrade php version on OSX
Tested on php 7.1, installed on local (OSX) with following command:
`curl -s https://php-osx.liip.ch/install.sh | bash -s 7.1`

Update .bash_profile to use new version of php `export PATH=/usr/local/php5/bin:$PATH;`

## Download Composer
[Composer](https://getcomposer.org) is a tool for dependency management in PHP. It allows you to declare the libraries your project depends on and it will manage (install/update) them for you.

```php
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '55d6ead61b29c7bdee5cccfb50076874187bd9f21f65d8991d46ec5cc90518f447387fb9f76ebae1fbbacf329e583e30') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
sudo php composer-setup.php
mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"
```

## Installing Lumen
[Lumen](https://lumen.laravel.com/) is a php micro framework to develope fast services. 
`composer global require "laravel/lumen-installer"`

Make sure to place the ~/.composer/vendor/bin directory in your PATH so the lumen executable can be located by your system.

## Setup Project
Use a starter `lumen new blog`

Create `ams/.env` to enable debugging
```
APP_ENV=local
APP_DEBUG=true
```

## Run Project Using Docker

You must have docker and docker-compose installed. Follow instructions [here](https://docs.docker.com/compose/install/)

In the project root execute:
```
docker-compose up -d
```
Then open the browser on http://localhost:8000 to see the project running;

