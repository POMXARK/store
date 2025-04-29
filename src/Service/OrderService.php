<?php

namespace App\Service;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class OrderService
{
    const SERVICES =  [
        'Оценка стоимости автомобиля' => 500,
        'Оценка стоимости квартиры' => 300,
        'Оценка стоимости бизнеса' => 1000,
    ];

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createOrder(string $email, string $price): Order
    {
        $order = new Order();
        $order->setEmail($email);
        $order->setService(array_search($price, OrderService::SERVICES));
        $order->setPrice($price);

        return $order;
    }

    /**
     * @throws Exception
     */
    public function saveOrder(Order $order): void
    {
        try {
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            error_log('Ошибка при сохранении заказа: ' . $e->getMessage());
            throw $e; // Можно выбросить исключение для обработки в контроллере
        }
    }
}
