<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LockObjectController extends AbstractController
{
    /**
     * @Route("/lockObject", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Response
     * @throws \Exception
     */
    public function number(\Symfony\Component\HttpFoundation\Request $request): Response
    {
        $number = random_int(0, 100);
        $response = new Response();
        $response->isOk();
        $response->setContent('I received something');
        return $response;
    }
}