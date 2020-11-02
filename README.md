# Класс для работы с IP-телефонией www.mango-office.ru

## Установка
```
composer require timur-turdyev/laravel-mango-office
```
## Подключение
В config/app.php в секции Package Service Providers <br />
`TimurTurdyev\MangoOffice\MangoServiceProvider::class,` <br />

## Конфигурация
Выполняем команду <br />
`php artisan vendor:publish` <br />

Затем выбрыть провайдера 
```
TimurTurdyev\MangoOffice\MangoServiceProvider
```
Будет создан конфигурационный файл config/mangooffice.php, где:
* api_key - Уникальный код АТС
* api_salt - Ключ для создания подписи

## Пример использования
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TimurTurdyev\MangoOffice\Mango;

class TestController extends Controller
{
    public function test()
    {
        $mango = new Mango;

        dd($mango->userList());
    }
}
```
## Доступные методы
```
// получить список всех пользователей<br>
$mango->userList();

// получить текущего пользователя<br>
$mango->userList('добавочный номер пользователя');

// получить статистику пользователя за указанный период<br>
$mango->reportList('UNIX формат начальная дата', 'UNIX формат конечная дата', 'внутренний номер абонента');

// получить статистику всех пользователей за указанный период<br>
$mango->reportList('начальная дата', 'конечная дата');

// скачать запись разговора<br>
$mango->downloadAudio('уникальный идентификатор записи');

// скачать запись разговора<br>
$mango->downloadAudio('уникальный идентификатор записи');

// воспроизвести запись разговора<br>
$mango->downloadAudio('уникальный идентификатор записи', 'play');
```
