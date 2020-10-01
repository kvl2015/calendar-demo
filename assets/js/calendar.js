import { Calendar } from '@fullcalendar/core';
import { toMoment, toDuration } from '@fullcalendar/moment';
import { formatDate } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
//import momentTimezonePlugin from '@fullcalendar/moment-timezone';

import ukLocale from '@fullcalendar/core/locales/uk';

var calendarEl = document.getElementById('calendar-holder');

var calendar = new Calendar(calendarEl, {
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    plugins: [ dayGridPlugin, timeGridPlugin, interactionPlugin ],
    editable: true,
    dayMaxEventRows: 5,
    locale: ukLocale,
    eventSources: [
        {
            url: "/fc-load-events",
            method: "POST",
            extraParams: {
                filters: JSON.stringify({})
            },
            failure: () => {
                // alert("There was an error while fetching FullCalendar!");
            },
        },
    ],
    eventClick: function(info) {
        let mStart = toMoment(info.event.start, calendar);
        let mEnd = toMoment(info.event.end, calendar);

        $.ajax({
            url: '/edit-event',
            data: {id: info.event.id},
            type: 'get',
            dataType: 'json',
            success: function(response) {
                $('.modal-content', $('#event-edit-modal')).html(response.html);
                $('#event-edit-modal').modal('show');

                $('#eventEdit', $("#event-edit-modal")).unbind().click(function() {
                    $.ajax({
                        url: '/update-event?id='+info.event.id,
                        data: $("#event-edit-modal").find('form').serialize(),
                        type: 'post',
                        dataType: 'json',
                        success: function(response) {
                            // if saved, close modal
                            $("#event-edit-modal").modal('hide');

                            // update selected event
                            calendar.refetchEvents();
                        }
                    });

                    return false;
                });

                $('#removeEvent', $("#event-edit-modal")).unbind().click(function() {
                    $.ajax({
                        url: '/remove-event?id='+info.event.id,
                        type: 'post',
                        dataType: 'json',
                        success: function(response) {
                            // delete selected event
                            info.event.remove();
                        }
                    });

                })
            }
        });
    },
    eventChange: function(info) {
        $.ajax({
            url: '/drag-event?id='+info.event.id,
            data: {id: info.event.id, date: info.event.start.toDateString(), startTime: info.event.start.toLocaleTimeString(), endTime: info.event.end.toLocaleTimeString()},
            type: 'post',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    calendar.refetchEvents();
                }
            }
        });
    },
    eventDrop: function(eventDropInfo) {
        /*$.ajax({
            url: '/drag-event?id='+eventDropInfo.event.id,
            data: {id: eventDropInfo.event.id, start: eventDropInfo.event.start.toISOString(), end: eventDropInfo.event.end.toISOString()},
            type: 'post',
            dataType: 'json',
            success: function(response) {
                calendar.refetchEvents();
            }
        });*/
    },
    dateClick: function(info) {
        let m = toMoment(info.date, calendar);
        $('#event-modal').modal('show');
        $('#booking_beginAt', $('#event-modal')).val(m.format('YYYY-MM-DD'));
        $('#booking_startTime', $('#event-modal')).val(m.format('HH:mm'));
        $('#booking_endTime', $('#event-modal')).val('23:59');

        $('#addNewEvent', $("#event-modal")).unbind().click(function() {
            $.ajax({
                url: '/add-event',
                data: $("#event-modal").find('form').serialize(),
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    // if saved, close modal
                    $("#event-modal").modal('hide');
                    calendar.addEvent({
                        title: response.title,
                        start: response.start,
                        end:response.end,
                        backgroundColor: response.color,
                        borderColor: response.color,
                        id: response.id
                    });
                    $("#event-modal").find('form')[0].reset();
                }
            });

            return false;
        })

        /*$("#event-modal").find('form').on('submit', function() {
            $.ajax({
                url: '/add-event',
                data: $("#event-modal").find('form').serialize(),
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    // if saved, close modal
                    $("#event-modal").modal('hide');
                    calendar.addEvent({
                        title: response.title,
                        start: response.start,
                        end:response.end,
                        backgroundColor: response.color,
                        borderColor: response.color,
                        id: response.id
                    });
                    $("#event-modal").find('form')[0].reset();
                }
            });

            return false;
        });*/

    },
    select: function( start, end, jsEvent, view ) {
        // set values in inputs
        //console.log(start);

        /*$('#event-modal').find('input[name=evtEnd]').val(
            end.format('YYYY-MM-DD HH:mm:ss')
        );*/


    },
    selectable: true,
});

calendar.render();

document.addEventListener('DOMContentLoaded', function() {



});

