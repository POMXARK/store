<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\OrderFormType;
use App\Service\OrderService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'order')]
    #[IsGranted('ROLE_USER')]
    public function order(Request $request, OrderService $orderService): Response
    {
        $form = $this->createForm(OrderFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $service = $form->get('service')->getData();

            // Проверка типов данных
            if (is_string($email) && is_int($service)) {
                try {
                    $order = $orderService->createOrder($email, $service);
                    $orderService->saveOrder($order);

                    return $this->redirectToRoute('order');
                } catch (Exception $e) {
                    error_log('Ошибка при сохранении заказа: '.$e->getMessage());
                }
            } else {
                error_log('Неверные типы данных для заказа.');
            }
        } else {
            // Логирование ошибок валидации
            $errors = $form->getErrors(true, false);
            foreach ($errors as $error) {
                if ($error instanceof FormError) {
                    error_log($error->getMessage());
                }
            }
        }

        return $this->render('order/order.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
