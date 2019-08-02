<?php

namespace App\Twig;

use App\Entity\Bill;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AmountLabelExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('amountLabel', [$this, 'amountLabel'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('amountLabel', [$this, 'amountLabel']),
        ];
    }

    public function amountLabel($amount, $type)
    {
        $amountLabel = '';
        if ($type == Bill::BILL_TYPE_PAY) {
            $amountLabel = '<span class="m--font-danger">R$ '.($amount > 0 ? '-' : '').$amount.'</span>';
        }
        if ($type == Bill::BILL_TYPE_RECEIVE) {
            $amountLabel = '<span class="m--font-success">R$ '.($amount > 0 ? '+' : '').$amount.'</span>';
        }

        return $amountLabel;
    }
}
