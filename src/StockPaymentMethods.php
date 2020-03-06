<?php

namespace App;

class StockPaymentMethods
{
    const DINHEIRO = 'dinheiro';
    const CARTAO = 'cartao';
    const CHEQUE = 'cheque';

    const PAYMENT_METHODS = [
        StockPaymentMethods::DINHEIRO => 'Dinheiro',
        StockPaymentMethods::CARTAO => 'Cartão',
        StockPaymentMethods::CHEQUE => 'Cheque',
    ];
}