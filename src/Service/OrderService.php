<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class OrderService
{
    /**
     * @var array<string, int> // Предполагаем, что услуги имеют строковые ключи и целочисленные значения
     */
    private array $services;
    private EntityManagerInterface $entityManager;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(private readonly ContainerBagInterface $params, EntityManagerInterface $entityManager, ?string $services = null)
    {
        // Если $services не передан, используем значение по умолчанию
        if (null === $services) {
            $this->services = (require $this->params->get('dictionaries'))['services'];
        } else {
            $this->services = require_once $services;
        }
        $this->entityManager = $entityManager;
    }

    /**
     * @throws Exception
     */
    public function createOrder(string $email, int $price): Order
    {
        $order = new Order();
        $order->setEmail($email);

        // Используем array_search, но обрабатываем случай, когда он может вернуть false
        $serviceKey = array_search($price, $this->services, true);
        if (false === $serviceKey) {
            throw new Exception('Услуга не найдена для указанной цены.');
        }

        $order->setService($serviceKey);
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
        } catch (Exception $e) {
            error_log('Ошибка при сохранении заказа: '.$e->getMessage());
            throw $e; // Повторно выбрасываем исключение для обработки в контроллере
        }
    }
}
