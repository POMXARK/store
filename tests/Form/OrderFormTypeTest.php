<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\OrderFormType;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Form\Test\TypeTestCase;

class OrderFormTypeTest extends TypeTestCase
{
    /**
     * @throws Exception
     */

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSubmitValidData()
    {
        $formData = [
            'service' => 500, // Выбор услуги
            'email' => 'test@example.com', // Корректный email
            'price' => 300, // Корректная цена
        ];

        // Создание формы с передачей необходимых параметров
        $form = $this->factory->create(OrderFormType::class, null, [
            'services' => (require dirname(__DIR__, 2).'/config/dictionaries.php')['services'],
        ]);

        // Подать данные в форму
        $form->submit($formData);

        // Проверить, что форма валидна
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        // Проверить, что данные формы соответствуют поданным
        $this->assertSame($formData['service'], $form->get('service')->getData());
        $this->assertSame($formData['email'], $form->get('email')->getData());
    }

    /**
     * При подтверждении заказа с незаполненной почтой или не выбранной
     * услугой пользователю отображается либо страница с ошибкой, либо та же самая
     * форма заказа с текстом ошибки в произвольном месте.
     */
    public function testSubmitInvalidData()
    {
        $formData = [
            'service' => null, // Не выбрана услуга
            'email' => '', // Пустой email
            'price' => null, // Пустая цена
        ];

        $form = $this->factory->create(OrderFormType::class);

        // Подать данные в форму
        $form->submit($formData);

        // Проверить, что форма валидирована
        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());

        // Проверить наличие ошибок валидации
        $this->assertCount(1, $form->get('service')->getErrors());
        $this->assertCount(1, $form->get('email')->getErrors());
        $this->assertCount(1, $form->get('price')->getErrors());

        // Проверить текст ошибок
        $this->assertSame('Пожалуйста, выберите услугу.', $form->get('service')->getErrors()[0]->getMessage());
        $this->assertSame('Email не должен быть пустым.', $form->get('email')->getErrors()[0]->getMessage());
        $this->assertSame('Цена не должна быть пустой.', $form->get('price')->getErrors()[0]->getMessage());
    }
}
