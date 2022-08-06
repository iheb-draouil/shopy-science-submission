<?php

namespace App\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function catchAll()
    {
        $ac = new ArrayCollection([1, 2, 3]);
        error_log('$ac[0]');
        error_log($ac[0]);
        return $this->render('page-not-found.html.twig');
    }
}