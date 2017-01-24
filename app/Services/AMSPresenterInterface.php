<?php
namespace App\Services;

interface AMSPresenterInterface
{
    public function present($data, $format, $echo);
}
