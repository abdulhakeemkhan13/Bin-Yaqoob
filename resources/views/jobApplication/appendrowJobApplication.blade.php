@php
    $application = $application ?? ($job ?? null);
    $logo    = $logo    ?? \App\Models\Utility::get_file('uploads/avatar/');
    $profile = $profile ?? \App\Models\Utility::get_file('uploads/job/profile/');
@endphp

<div class="card" data-id="{{ $application->id }}" data-row_id="{{ $application->id }}">
    <div class="pt-3 ps-3"></div>

    <div class="card-header border-0 pb-0 position-relative">
        <h5>
            <a href="{{ route('job-application.show', \Crypt::encrypt($application->id)) }}">
                {{ $application->name }}
            </a>
        </h5>
        <div class="card-header-right">
            @if(Auth::user()->type != 'client')
                <div class="btn-group card-option">
                    <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        @can('show job application')
                            <a class="dropdown-item" href="{{ route('job-application.show', \Crypt::encrypt($application->id)) }}">
                                <i class="ti ti-bookmark"></i>{{ __('View') }}
                            </a>
                        @endcan
                        @can('delete job application')
                            {!! Form::open(['method' => 'DELETE', 'route' => ['job-application.destroy', $application->id], 'id' => 'delete-form-'.$application->id]) !!}
                            <a href="#!" class="dropdown-item bs-pass-para">
                                <i class="ti ti-archive"></i><span> {{ __('Delete') }} </span>
                            </a>
                            {!! Form::close() !!}
                        @endcan
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            <ul class="list-inline mb-0 mt-0">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <span class="static-rating static-rating-sm d-block">
                            @for($i=1; $i<=5; $i++)
                                @if($i <= ($application->rating ?? 0))
                                    <i class="star fas fa-star voted"></i>
                                @else
                                    <i class="star fas fa-star"></i>
                                @endif
                            @endfor
                        </span>
                    </div>
                </div>
                <small class="text-md">{{ optional($application->jobs)->title }}</small><br>
                <li class="list-inline-item d-inline-flex align-items-center" title="{{__('Applied at')}}">
                    <i class="ti ti-clock me-1"></i>{{ \Auth::user()->dateFormat($application->created_at) }}
                </li>
            </ul>
            <div class="user-group">
                <img
                    src="{{ !empty($application->profile) ? $profile . $application->profile : $logo.'avatar.png' }}"
                    class="hweb">
            </div>
        </div>
    </div>
</div>
