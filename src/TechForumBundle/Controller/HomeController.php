<?php

namespace TechForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    /**
     * @Route("/", name="forum_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }
}
