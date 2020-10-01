<?php


namespace App\EventSubscriber;

use App\Repository\BookingRepository;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    private $bookingRepository;
    private $router;

    public function __construct(
        BookingRepository $bookingRepository,
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar)
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        /*if (!$token = $this->tokenStorage->getToken()) {
            return ;
        }*/
        $isAdmin = false;
        $user = $this->tokenStorage->getToken()->getUser();
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $isAdmin = true;
        }

        // Modify the query to fit to your entity and needs
        // Change booking.beginAt by your start date property
        $bookings = $this->bookingRepository
            ->createQueryBuilder('booking')
            ->where('booking.beginAt > :start')
            ->setParameter('start', $start->format('Y-m-d'))
            ->getQuery()
            ->getResult()
        ;

        foreach ($bookings as $booking) {
            // this create the events with your data (here booking data) to fill calendar
            $bookingEvent = new Event(
                $booking->getTitle(),
                \DateTime::createFromFormat('Y-m-d H:i', $booking->getBeginAt()->format('Y-m-d').' '.$booking->getStartTime()->format('H:i')),
                \DateTime::createFromFormat('Y-m-d H:i', $booking->getBeginAt()->format('Y-m-d').' '.$booking->getEndTime()->format('H:i')) // If the end date is null or not defined, a all day event is created.
            );

            /*
             * Add custom options to events
             *
             * For more information see: https://fullcalendar.io/docs/event-object
             * and: https://github.com/fullcalendar/fullcalendar/blob/master/src/core/options.ts
             */

            $bookingEvent->setOptions([
                'backgroundColor' => $booking->getColor(),
                'borderColor' => $booking->getColor(),
                'id' => $booking->getId(),
                'editable' => $isAdmin ? true : $booking->getAuthor()->getId() == $user->getId() ? true : false
            ]);
            /*$bookingEvent->addOption(
                'url',
                $this->router->generate('booking_show', [
                    'id' => $booking->getId(),
                ])
            );*/
            $bookingEvent->addOption(
                'url',
                'javascript:;'
            );

            // finally, add the event to the CalendarEvent to fill the calendar
            $calendar->addEvent($bookingEvent);
        }
    }
}