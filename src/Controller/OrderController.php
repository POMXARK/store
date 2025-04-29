<?php

namespace App\Controller;

use App\Form\OrderFormType;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        if ($form->isSubmitted()) {
            $order = $orderService->createOrder($form->get('email')->getData(), $form->get('service')->getData());
            try {
                $orderService->saveOrder($order);
                return $this->redirectToRoute('order');
            } catch (\Exception $e) {
                error_log('Ошибка при сохранении заказа: ' . $e->getMessage());
            }
        } else {
            // Логирование ошибок валидации
            $errors = $form->getErrors(true, false);
            foreach ($errors as $error) {
                error_log($error->getMessage());
            }
        }

        return $this->render('order/order.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
