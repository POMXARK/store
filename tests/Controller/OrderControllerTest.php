<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private User $user;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
    }

    /**
     * При открытии формы заказа не авторизованным пользователем отображается
     * страница с ошибкой.
     *
     * @return void
     */
    public function testOrderPageForAnonymousUser()
    {
        $client = static::createClient();
        $client->request('GET', '/order');

        // Проверяем, что перенаправляет на страницу входа
        $this->assertResponseRedirects('/login');
        $client->followRedirect();

        // Проверяем, что на странице входа отображается форма
        $this->assertSelectorExists('form');
    }

    private function createUser(): void
    {
        $user = new User();
        $user->setEmail('test_user_'.uniqid().'@example.com'); // Уникальный email
        $user->setPassword('password'); // Установите пароль

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->user = $user;
    }

    /**
     * Авторизованному пользователю отображается форма, проверить, что вывелись все
     * поля и кнопка.
     *
     * @return void
     */
    public function testOrderPageForAuthenticatedUser()
    {
        $client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->createUser(); // Сохраняем пользователя в свойство

        $client->loginUser($this->user);

        $client->request('GET', '/order');

        // Проверяем, что страница загружена успешно
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('select[name="order_form[service]"]'); // Проверяем наличие поля выбора услуги
        $this->assertSelectorExists('input[name="order_form[email]"]'); // Проверяем наличие поля email
        $this->assertSelectorExists('button[type="submit"]'); // Проверяем наличие кнопки отправки формы

        $this->entityManager->clear();
    }

    /**
     * При отправке авторизованным пользователем формой с заполненными полями B
     * хранилище должен появиться новый заказ с данными, соответствующими форме.
     *
     * @return void
     */
    public function testCreateOrder()
    {
        $client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->createUser(); // Создаем и сохраняем пользователя
        $client->loginUser($this->user); // Авторизуем пользователя

        // Подготовка данных для отправки формы
        $formData = [
            'order_form' => [
                'service' => 500, // ID услуги
                'email' => $this->faker->email(), // Корректный email
                // 'price' будет установлен автоматически
            ],
        ];

        // Отправка формы
        $client->request('POST', '/order', $formData);

        // Проверяем, что редирект произошел после успешного создания заказа
        $this->assertResponseRedirects('/order');

        // Проверяем, что заказ был создан в базе данных
        $this->entityManager->clear(); // Очищаем EntityManager, чтобы избежать кэширования

        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['email' => $formData['order_form']['email']]);

        // Проверяем, что заказ существует и его данные соответствуют ожидаемым
        $this->assertNotNull($order);
        $this->assertEquals('Оценка стоимости автомобиля', $order->getService()); // Проверка ID услуги
        $this->assertEquals($formData['order_form']['email'], $order->getEmail()); // Проверка email
        $this->assertEquals(500, $order->getPrice()); // Проверка email
    }
}
