<?php
namespace App\Controller;

use App\Entity\LockObject;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LockObjectController extends AbstractController
{
    /**
     * @Route("/lockObject", methods={"POST"})
     * @param Request $request
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function processLockObjectRequest(Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager): Response
    {
        $response = new Response();
        $data = json_decode($request->getContent(), true);

        if($this->isDataValid($data, $logger)) {
            $lockObject = new LockObject();
            $lockObject->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', $data['timeStamp']));
            $lockObject->setLocked($data['locked']);
            $lockObject->setMessage($data['message']);
            $lockObject->setUserId($data['userId']);

            try {
                $entityManager->persist($lockObject);
                $entityManager->flush();
                $response->isOk();
            }
            catch(ORMException $e) {
                $response->setContent('Database error');
                $response->isInvalid();
            }
        }
        else {
            $response->setContent('Invalid data');
            $response->isInvalid();
        }

        return $response;
    }

    /**
     * @Route("/", methods={"GET"})
     * @return Response
     */
    public function lockObjectOverview()
    {
        $lockObjects = $this->getDoctrine()->getRepository(LockObject::class)->findAll();
        return $this->render('base.html.twig',
            [
                'title' => 'overview',
                'lockObjects' => $lockObjects
            ]
        );
    }

    /**
     * @Route("/filterLockObject", methods={"POST"})
     * @param Request $request
     * @return void
     */
    public function lockObjectFilterOverview(Request $request)
    {
        dump($request); exit();
    }

    private function isDataValid(array $data, LoggerInterface $logger) : bool
    {
        if(
            isset($data['userId']) &&
            is_string($data['userId']) &&
            isset($data['timeStamp']) &&
            $data['timeStamp'] &&
            is_string($data['timeStamp']) &&
            isset($data['locked']) &&
            is_bool($data['locked']) &&
            isset($data['location']) &&
            is_string($data['location']) &&
            isset($data['message']) &&
            is_string($data['message'])
        )
        {
            $logger->log('info', json_encode($data) . ' is valid');
            return true;
        }
        else
        {
            $logger->log('info', json_encode($data) . ' is invalid');
            return false;
        }
    }
}