<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Model\OrderData;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormType extends AbstractType
{
    public function __construct(private readonly ?ContainerBagInterface $params = null)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', ChoiceType::class, [
                'choices' => $options['services'],
                'placeholder' => 'Выберите услугу',
            ])
            ->add('email', EmailType::class)
            ->add('price', MoneyType::class, [
                'attr' => ['readonly' => 'readonly'],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Заказать'])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                /** @var OrderData $data */
                $data = $event->getData();

                $form = $event->getForm();

                // Валидация для поля service
                if (empty($data->service)) {
                    $form->get('service')->addError(new FormError('Пожалуйста, выберите услугу.'));
                }

                // Валидация для поля email
                if (empty($data->email)) {
                    $form->get('email')->addError(new FormError('Email не должен быть пустым.'));
                }

                // Валидация для поля price
                if (empty($data->price)) {
                    $form->get('price')->addError(new FormError('Цена не должна быть пустой.'));
                }
            });
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderData::class, // Указываем класс данных
            'services' => $this->params ? (require $this->params->get('dictionaries'))['services'] : [],
        ]);
    }
}
