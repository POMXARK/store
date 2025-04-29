<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', ChoiceType::class, [
                'choices' => [
                    'Оценка стоимости автомобиля' => 500,
                    'Оценка стоимости квартиры' => 300,
                    'Оценка стоимости бизнеса' => 1000,
                ],
                'placeholder' => 'Выберите услугу',
            ])
            ->add('email', EmailType::class)
            ->add('price', MoneyType::class, [
                'attr' => ['readonly' => 'readonly'],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Заказать'])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $form->getData();

                // Валидация для поля service
                if (empty($data['service'])) {
                    $form->get('service')->addError(new FormError('Пожалуйста, выберите услугу.'));
                }

                // Валидация для поля email
                if (empty($data['email'])) {
                    $form->get('email')->addError(new FormError('Email не должен быть пустым.'));
                }

                // Валидация для поля price
                if (empty($data['price'])) {
                    $form->get('price')->addError(new FormError('Цена не должна быть пустой.'));
                }
            });
    }
}
