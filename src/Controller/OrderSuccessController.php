<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_success")
     */
    public function index($stripeSessionId, Cart $cart): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() != $this->getUser()){

            return $this->redirectToRoute('home');
        }

        if (!$order->getState()){
            $cart->remove();

            $order->setState(1);
            $this->entityManager->flush();

            $mail = new Mail();
            $content = "Bonjour".$order->getUser()->getFirstname().",<br> Merci pour votre commande sur la première boutique dédiée au Made in France.<br><br>";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Validation de commande La Boutique Française', $content);

        }

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
