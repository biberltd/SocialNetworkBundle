<?php

namespace BiberLtd\Bundle\SocialNetworkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BiberLtdSocialNetworkBundle:Default:index.html.twig', array('name' => $name));
    }
}
