@extends('layouts.admin')
@push('script-page')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    @if (\Auth::user()->type == 'company')
        <script>
            $(function() {
                $(".sortable").sortable();
                $(".sortable").disableSelection();
                $(".sortable").sortable({
                    stop: function() {
                        var order = [];
                        $(this).find('li').each(function(index, data) {
                            order[index] = $(data).attr('data-id');
                        });

                        $.ajax({
                            url: "{{ route('job.stage.order') }}",
                            data: {
                                order: order,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            success: function(data) {},
                            error: function(data) {
                                data = data.responseJSON;
                                toastr('Error', data.error, 'error')
                            }
                        })
                    }
                });
            });
        </script>
    @endif
@endpush
@section('page-title')
    {{ __('Manage Job Stage') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Job Stage') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create job stage')
            <a href="#" data-url="{{ route('job-stage.create') }}" data-ajax-popup="true"
                data-title="{{ __('Create New Job Stage') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content tab-bordered">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <ul class="list-unstyled list-group sortable stage" id="jobstage-table">
                                @foreach ($stages as $stage)
                                    <li class="d-flex align-items-center justify-content-between list-group-item"
                                        data-id="{{ $stage->id }}" data-row-id="{{ $stage->id }}">
                                        <h6 class="mb-0">
                                            <i class="me-3 ti ti-arrows-maximize " data-feather="move"></i>
                                            <span>{{ $stage->title }}</span>
                                        </h6>
                                        <span class="float-end">
                                            @can('edit job stage')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-url="{{ route('job-stage.edit', $stage->id) }}"
                                                        data-ajax-popup="true" data-title="{{ __('Edit Job Stage') }}"
                                                        data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                        data-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete job stage')
                                                {{-- <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['job-stage.destroy', $stage->id],'id'=>'delete-form-'.$stage->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
                                                        {!! Form::close() !!}
                                                     </div> --}}
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'route' => ['job-stage.destroy', $stage->id],
                                                        'id' => 'delete-form-' . $stage->id,
                                                    ]) !!}
                                                    <button type="button"
                                                        class="mx-3 btn btn-sm align-items-center delete-confirm"
                                                        data-form="delete-form-{{ $stage->id }}"
                                                        data-title="{{ __('Are you sure?') }}"
                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                        data-yes="{{ __('Yes') }}" data-no="{{ __('No') }}"
                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </button>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <p class=" mt-4"><strong>{{ __('Note') }} :
                        </strong><b>{{ __('You can easily change order of job stage using drag & drop.') }}</b></p>

                </div>
            </div>

        </div>
    </div>
    @push('script-page')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Delegate clicks for any .delete-confirm button
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.delete-confirm');
                if (!btn) return;

                e.preventDefault();

                const formId = btn.getAttribute('data-form');
                const title = btn.getAttribute('data-title') || 'Are you sure?';
                const text = btn.getAttribute('data-text') || 'This action cannot be undone.';
                const yesLbl = btn.getAttribute('data-yes') || 'Yes';
                const noLbl = btn.getAttribute('data-no') || 'No';

                Swal.fire({
                    icon: 'warning',
                    title: title,
                    text: text,
                    showCancelButton: true,
                    confirmButtonText: yesLbl,
                    cancelButtonText: noLbl,
                    reverseButtons: true,
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-success mx-1',
                        cancelButton: 'btn btn-danger mx-1',
                        popup: 'rounded'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(formId);
                        if (form) form.submit(); // normal submit -> controller redirect -> full refresh
                    }
                });
            });
        </script>
    @endpush
@endsection
