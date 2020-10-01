<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use CalendarBundle\Entity\Event;
use Cassandra\Date;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Constraints\DateTime;


/**
 * @Route("/booking")
 */
class BookingController extends AbstractController
{
    /**
     * @Route("/", name="booking_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(BookingRepository $bookingRepository): Response
    {
        dd($bookingRepository->findExpiered());
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);

        return $this->render('booking/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/add-event", name="booking_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request)
    {
        $booking = new Booking();
        $booking->setAuthor($this->getUser());
        $booking->setStatus('new');

        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($booking);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'id' => $booking->getId(),
                    'title' => $booking->getTitle(),
                    'start' => $booking->getBeginAt()->format('Y-m-d').'T'.$booking->getStartTime()->format('H:i'),
                    'end' => $booking->getBeginAt()->format('Y-m-d').'T'.$booking->getEndTime()->format('H:i'),
                    'color' => $booking->getColor()
                ]);
            }
        } else {
            return $this->json([
                'success' => false,
            ]);
        }
    }

    /**
     * @Route("/edit-event", name="calendar_edit_event", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function edit(Request $request)
    {
        $booking = $this->getDoctrine()
            ->getRepository(Booking::class)
            ->find($request->query->get('id'));

        // edit event
        if ($this->getUser()->getId() == $booking->getAuthor()->getId() || in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $form = $this->createForm(BookingType::class, $booking);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
                return $this->json([
                    'success' => true,
                    'id' => $booking->getId(),
                    'title' => $booking->getTitle(),
                    'start' => $booking->getBeginAt()->format('Y-m-d').'T'.$booking->getStartTime()->format('H:i'),
                    'end' => $booking->getBeginAt()->format('Y-m-d').'T'.$booking->getEndTime()->format('H:i'),
                    'color' => $booking->getColor()
                ]);
            } else {
                return $this->json(
                    [
                        'success' => true,
                        'type' => 'edit',
                        'html' => $this->renderView('modals/_form.html.twig', [
                            'booking' => $booking,
                            'form' => $form->createView(),
                        ])
                    ]
                );
            }
        } else { // view event
            return $this->json(
                [
                    'success' => true,
                    'type' => 'view',
                    'html' => $this->renderView('modals/_show.html.twig', [
                        'booking' => $booking,
                    ])
                ]
            );
        }
    }


    /**
     * @Route("/drag-event", name="calendar_drag_event", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function drag(Request $request)
    {
        $booking = $this->getDoctrine()
            ->getRepository(Booking::class)
            ->find($request->query->get('id'));

        if ($this->getUser()->getId() == $booking->getAuthor()->getId() || in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $booking->setBeginAt(new \DateTime(date("Y-m-d", strtotime($request->request->get('date')))));
            $booking->setStartTime(new \DateTime($request->request->get('startTime')));
            $booking->setEndTime(new \DateTime($request->request->get('endTime')));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($booking);
            $entityManager->flush();

            return $this->json(
                [
                    'success' => true,
                ]
            );
        } else {
            return $this->json(
                [
                    'success' => false,
                ]
            );
        }

        $booking->setBeginAt(new \DateTime(date("Y-m-d", strtotime($request->request->get('date')))));
        $booking->setStartTime(new \DateTime($request->request->get('startTime')));
        $booking->setEndTime(new \DateTime($request->request->get('endTime')));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($booking);
        $entityManager->flush();

        return $this->json(
            [
                'success' => true,
            ]
        );
    }


    /**
     * @Route("/remove-event", name="calendar_remove_event", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function delete(Request $request): Response
    {
        $booking = $this->getDoctrine()
            ->getRepository(Booking::class)
            ->find($request->query->get('id'));

        if ($this->getUser()->getId() == $booking->getAuthor()->getId() || in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        return $this->json(
            [
                'success' => true,
            ]
        );
    }
}
