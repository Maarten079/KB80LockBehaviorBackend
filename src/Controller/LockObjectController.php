<?php
namespace App\Controller;

use App\Entity\LockObject;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
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
     */
    public function lockObjectFilterOverview(Request $request)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getDoctrine()->getRepository(LockObject::class)->getQueryBuilder();

        // Always check for name to prevent false andWhere
        $qb->where('l.userId like :participant');
        $qb->setParameter('participant', '%' . $request->get('participant') . '%');

        if (
            $request->get('from-date') &&
            !empty($request->get('from-date'))
        ) {
            $qb->andWhere('l.createdAt > :fromDate');
            $qb->setParameter('fromDate', $request->get('from-date'));
        }

        if (
            $request->get('to-date') &&
            !empty($request->get('to-date'))
        )
        {
            $qb->andWhere('l.createdAt < :toDate');
            $qb->setParameter('toDate', $request->get('to-date'));
        }

        if(
            (int) $request->get('lock-status') !== 2
        )
        {
            $qb->andWhere('l.locked = :lockStatus');
            $qb->setParameter('lockStatus', $request->get('lock-status') == 1);
        }

        if(
            $request->get('message') &&
            !empty($request->get('message'))
        )
        {
            $qb->andWhere('l.message like :message');
            $qb->setParameter('message', '%' . $request->get('message') . '%');
        }

        try {
        $lockObjects = $qb->getQuery()->execute();

        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('base.html.twig',
            [
                'title' => 'overview',
                'lockObjects' => $lockObjects
            ]
        );
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