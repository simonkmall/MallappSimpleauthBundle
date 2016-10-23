<?php

namespace Mallapp\SimpleauthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('MallappSimpleauthBundle:Default:index.html.twig');
    }
}
