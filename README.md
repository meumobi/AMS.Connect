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

###Run composer
When run ```$ composer install``` if you got 
```bash
 Problem 1
    - The requested PHP extension ext-imap * is missing from your system. Install or enable PHP's imap extension.
  Problem 2
    - The requested PHP extension ext-memcached * is missing from your system. Install or enable PHP's memcached extension.
```
```php
$ composer install --ignore-platform-reqs
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

# Keys not found
keys-not-found script tail logs to catch keys not found messages.

`$scripts/> keys-not-found`

```
AdServing Key Not Found "616770-15"
AdServing Key Not Found "616772-15"
Correlation Key Not Found "392902-10"} []
Correlation Key Not Found "394166-10"} []
Correlation Key Not Found "394466-101"} []
```

# Contributing
## Workflow

1. Fork it
2. Create your feature branch (git checkout -b my-new-feature)
3. Commit your changes (git commit -am 'Add some feature')
4. Push to the branch (git push origin my-new-feature)
5. Create new Pull Request

## Branch naming
### `<type>/<name>`

#### `<type>`
```
bug    - Code changes linked to a known issue.
feat   - New feature.
hotfix - Quick fixes to the codebase.
junk   - Experiments (will never be merged).
```

#### `<name>`
Always use dashes to seperate words, and keep it short.

#### Examples
```
feat/renderer-cookies
hotfix/dockerfile-base-image
bug/login-ie
```

## Commit msg
### `<type>: Closes #<issue-id>, <title>`

#### `<type>`
```
FIX         - Code changes linked to a known issue.
FEATURE     - New feature.
HOTFIX      - Quick fixes to the codebase.
ENHANCEMENT - Update of existing feature.
UPGRADE     - Upgrade of 3rd party lib.
DOC         - Documentation.
```

#### Examples
```
BUG: Closes #69, Hide Stock widget if reponse empty
UPGRADE: Closes #96, Upgrade libraries versions and set App version
```

