<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage booking contents in the backend.
 *
 * @Route("/admin/booking")
 *
 *
 * @author KVL <victoriya.korogod@gmail.com>
 */
class BookingController extends AbstractController
{
    /**
     * Lists all Booking entities.
     *
     * This controller responds to two different routes with the same URL:
     *   * 'admin_post_index' is the route with a name that follows the same
     *     structure as the rest of the controllers of this class.
     *   * 'admin_index' is a nice shortcut to the backend homepage. This allows
     *     to create simpler links in the templates. Moreover, in the future we
     *     could move this annotation to any other controller while maintaining
     *     the route name and therefore, without breaking any existing link.
     *
     * @Route("/", methods="GET", name="admin_index")
     * @Route("/", methods="GET", name="admin_booking_index")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(BookingRepository $booking): Response
    {
        $authorBooking = $booking->findAll();

        return $this->render('admin/booking/index.html.twig', ['bookings' => $authorBooking]);
    }

    /**
     * Creates a new Booking entity.
     *
     * @Route("/new", methods="GET|POST", name="admin_booking_new")
     */
    public function new(Request $request): Response
    {
        $booking = new Booking();
        $booking->setAuthor($this->getUser());
        $booking->setStatus('new');
        $booking->setColor('#000');

        $form = $this->createForm(BookingType::class, $booking)
            ->add('saveAndCreateNew', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($booking);
            $em->flush();

            $this->addFlash('success', 'booking.created_successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_booking_new');
            }

            return $this->redirectToRoute('admin_booking_index');
        }

        return $this->render('admin/booking/new.html.twig', [
            'booking' => $booking,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a Booking entity.
     *
     * @Route("/{id<\d+>}", methods="GET", name="admin_booking_show")
     */
    public function show(Booking $booking): Response
    {
        return $this->render('admin/booking/show.html.twig', [
            'booking' => $booking,
        ]);
    }

    /**
     * Displays a form to edit an existing Booking entity.
     *
     * @Route("/{id<\d+>}/edit", methods="GET|POST", name="admin_booking_edit")
     */
    public function edit(Request $request, Booking $booking): Response
    {
        if ($booking->getAuthor() !== $this->getUser() && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'row.updated_successfully');

            return $this->redirectToRoute('admin_post_edit', ['id' => $booking->getId()]);
        }

        return $this->render('admin/booking/edit.html.twig', [
            'booking' => $booking,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}/delete", methods="POST", name="admin_booking_delete")
     * @IsGranted("delete", subject="post")
     */
    public function delete(Request $request, Post $post): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_post_index');
        }

        // Delete the tags associated with this blog post. This is done automatically
        // by Doctrine, except for SQLite (the database used in this application)
        // because foreign key support is not enabled by default in SQLite
        $post->getTags()->clear();

        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'post.deleted_successfully');

        return $this->redirectToRoute('admin_post_index');
    }
}
