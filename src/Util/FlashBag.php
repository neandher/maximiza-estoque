<?php

namespace App\Util;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface as BaseFlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class FlashBag
{

    /**
     * @var BaseFlashBag
     */
    private $flashBag;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(BaseFlashBag $flashBag, TranslatorInterface $translator = null)
    {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function newMessage($type, $message, array $params = [], Session $previousSession = null)
    {
        $message = $this->translator === null ? $message : $this->translator->trans($message, $params);

        if ($previousSession) {
            $previousSession->getFlashBag()->add($type, $message);
        } else {
            $this->flashBag->add($type, $message);
        }
    }
}