@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
@endpush

<div id="availability-calendar" class="h-[600px]"></div>

<input type="hidden" name="availability" id="availability-input">

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('availability-calendar');
            const input = document.getElementById('availability-input');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                editable: true,
                selectable: true,
                allDaySlot: false,
                slotMinTime: "07:00:00",
                slotMaxTime: "21:00:00",
                events: {!! json_encode($operator?->availability ?? []) !!},
                select: function(info) {
                    calendar.addEvent({
                        start: info.start,
                        end: info.end,
                        daysOfWeek: [info.start.getDay()],
                        title: 'Disponibile'
                    });
                    updateHiddenInput();
                },
                eventChange: updateHiddenInput,
                eventRemove: updateHiddenInput,
            });

            function updateHiddenInput() {
                const events = calendar.getEvents().map(e => ({
                    start: e.start.toISOString(),
                    end: e.end.toISOString(),
                    dow: e.start.getDay()
                }));
                input.value = JSON.stringify(events);
            }

            calendar.render();
        });
    </script>
@endpush
