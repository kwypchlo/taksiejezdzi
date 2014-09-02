<?php

namespace tsj\memsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('tsjmemsBundle:Default:index.html.twig', array('name' => $name));
    }
}
