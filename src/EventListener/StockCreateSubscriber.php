<?php

namespace App\EventListener;

use App\Entity\Stock;
use App\Entity\User;
use App\Event\StockEvents;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StockCreateSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var UserRepository
     */
    private $userRepository;

    private $emailFromAddress;
    private $adminTitle;

    /**
     * StockCreateSubscriber constructor.
     * @param \Swift_Mailer $mailer
     * @param TokenStorageInterface $tokenStorage
     * @param \Twig_Environment $twig
     * @param UserRepository $userRepository
     * @param $emailFromAddress
     * @param $adminTitle
     */
    public function __construct(
        \Swift_Mailer $mailer,
        TokenStorageInterface $tokenStorage,
        \Twig_Environment $twig,
        UserRepository $userRepository,
        $emailFromAddress,
        $adminTitle
    )
    {
        $this->mailer = $mailer;
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->emailFromAddress = $emailFromAddress;
        $this->adminTitle = $adminTitle;
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            StockEvents::STOCK_CREATE_COMPLETED => 'onCreateCompleted'
        ];
    }

    /**
     * @param GenericEvent $event
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onCreateCompleted(GenericEvent $event)
    {
        /** @var Stock[] $stocks */
        $stocks = $event->getSubject();
        /** @var User[] $users */
        $users = $this->userRepository->queryLatestForm()->getQuery()->getResult();

        $emails = [];
        foreach ($users as $user) {
            if ($user->getReceiveEmails()) {
                $emails[] = $user->getEmailNotifications() ? $user->getEmailNotifications() : $user->getEmail();
            }
        }

        $message = (new \Swift_Message())
            ->setSubject('Nova movimentação de estoque')
            ->setFrom([$this->emailFromAddress => $this->adminTitle])
            ->setTo($emails)
            ->setBody(
                $this->twig->render('admin/stock/email.html.twig', [
                    'stocks' => $stocks,
                    'user' => $this->tokenStorage->getToken()->getUser()
                ]),
                'text/html'
            );

        $this->mailer->send($message);
    }
}