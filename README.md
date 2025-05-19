```shell
php bin/console doctrine:schema:create --env=test
vendor/bin/phpunit
```

Встроенный сервер:
```shell
symfony server:start
```

Исправить форматирование:
```shell
php php-cs-fixer.phar fix
phpstan analyse
``` 