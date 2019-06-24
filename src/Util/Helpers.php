<?php

namespace App\Util;

use Symfony\Component\Form\FormInterface;

class Helpers
{

    public static function getErrorsFromForm(FormInterface $form, $return = 'array')
    {
        $str = '';
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = self::getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                    $str .= $childErrors[0] . ' / ';
                }
            }
        }
        return $return == 'array' ? $errors : substr($str, 0, strlen($str) - 3);
    }
}