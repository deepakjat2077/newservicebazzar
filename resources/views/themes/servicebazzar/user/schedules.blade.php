@extends($themeView.'layouts.app')
@section('content')
    @push('css')
        <style>
            .card-header{
                background: #ff5252;
                color: #fff;
            }
            .card-title {
                margin-bottom: -0.25rem;
            }
        </style>
    @endpush
    @section('title','Schedules')
    <section class="pt-80 pb-80">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon"><i class="las la-clock"></i></span>
                                <span class="p-md-1">@lang('Daily Time Schedule')</span>
                            </h5>
                        </div>
                        <div class="card-body" id="schedule">
                            @php
                                $days = [
                                    1 => 'monday',
                                    2 => 'tuesday',
                                    3 => 'wednesday',
                                    4 => 'thursday',
                                    5 => 'friday',
                                    6 => 'saturday',
                                    0 => 'sunday',
                                ];

                                $data = [];
                                foreach ($store?->schedules as $schedule) {
                                    $data[$schedule->day][] = [
                                        'id' => $schedule->id,
                                        'start_time' => $schedule->opening_time,
                                        'end_time' => $schedule->closing_time
                                    ];
                                }
                            @endphp

                            @foreach($days as $dayId => $dayName)
                                <div class="schedule-item">
                                    <span class="btn">{{ ucwords(__('' . $dayName)) }} :</span>
                                    <div class="schedult-date-content">
                                        @if(!empty($data[$dayId]))
                                            <span class="d-inline-flex align-items-center">
                                                @foreach ($data[$dayId] as $day)
                                                    <span class="start--time">
                                                        <span class="clock--icon">
                                                            <i class="las la-clock"></i>
                                                        </span>
                                                        <span class="info">
                                                            <span>@lang('Opening Time')</span>
                                                            {{ date('h:i A', strtotime($day['start_time'])) }}
                                                        </span>
                                                    </span>
                                                    <span class="end--time">
                                                        <span class="clock--icon">
                                                            <i class="las la-clock"></i>
                                                        </span>
                                                        <span class="info">
                                                            <span>@lang('Closing Time')</span>
                                                            {{ date('h:i A', strtotime($day['end_time'])) }}
                                                        </span>
                                                    </span>
                                                    <span class="dismiss--date delete-schedule"
                                                          data-toggle="modal"
                                                          data-target="#removeModal"
                                                          data-url="{{ route('user.schedule.removed', $day['id']) }}">
                                                        <i class="las la-trash-alt"></i>
                                                    </span>
                                                @endforeach
                                            </span>
                                        @else
                                            <span class="btn btn-sm btn-outline-danger m-1 disabled">
                                                @lang('Offday')
                                            </span>
                                        @endif
                                        <span class="btn add--primary"
                                              data-toggle="modal"
                                              data-target="#exampleModal"
                                              data-dayid="{{ $dayId }}"
                                              data-day="{{ ucwords(__('' . $dayName)) }}">
                                            <i class="las la-plus"></i>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Create schedule modal -->

                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">@lang('Create Schedule')</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form action="" method="post" id="add-schedule">
                                        @csrf
                                        <input type="hidden" name="day" id="day_id_input">
                                        <div class="form-group">
                                            <label for="recipient-name" class="col-form-label">@lang('Start Time'):</label>
                                            <input type="time" class="form-control" name="start_time" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="message-text" class="col-form-label">@lang('End Time'):</label>
                                            <input type="time" class="form-control" name="end_time" required>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" class="btn btn__base"><i class="far fa-check-circle"></i> @lang('Save')</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="removeModal" tabindex="-1" role="dialog" aria-labelledby="removeModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="removeModalLabel">@lang('Removed Schedule')</h6>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <h5 class="text-center">@lang('Are you sure to remove this schedule?')</h5>
                                </div>
                                <form method="get" enctype="multipart/form-data" action="">
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-danger">Remove</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            $('#exampleModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var day_name = button.data('day');
                var day_id = button.data('dayid');
                var modal = $(this);
                modal.find('.modal-title').text('@lang('Create Schedule For ') ' + day_name);
                modal.find('.modal-body input[name=day]').val(day_id);
            })

            $('#removeModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var url = button.data('url');
                var modal = $(this);
                modal.find('form').attr('action', url);
            });
        });
    </script>
@endpush
