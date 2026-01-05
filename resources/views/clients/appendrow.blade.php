<div class="col-md-3" data-row_id="{{$client->id}}">
    <div class="card text-center">
        <div class="card-header border-0 pb-0">

            <div class="card-header-right">
                <div class="btn-group card-option">
                    <button type="button" class="btn dropdown-toggle"
                            data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                        <i class="ti ti-dots-vertical"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
{{--                                            <a href="{{ route('clients.show',$client->id) }}"  class="dropdown-item" data-bs-original-title="{{__('View')}}">--}}
{{--                                                <i class="ti ti-eye"></i>--}}
{{--                                                <span>{{__('Show')}}</span>--}}
{{--                                            </a>--}}

                        @can('edit client')
                            <a href="#!" data-size="md" data-url="{{ route('clients.edit',$client->id) }}" data-ajax-popup="true" class="dropdown-item" data-bs-original-title="{{__('Edit Client')}}">
                                <i class="ti ti-pencil"></i>
                                <span>{{__('Edit')}}</span>
                            </a>
                        @endcan

                        @can('delete client')
                            {!! Form::open(['method' => 'DELETE', 'route' => ['clients.destroy', $client['id']],'id'=>'delete-form-'.$client['id']]) !!}
                            <a href="#!"  class="dropdown-item bs-pass-para">
                                <i class="ti ti-archive"></i>
                                <span> @if($client->delete_status!=0){{__('Delete')}} @else {{__('Restore')}}@endif</span>
                            </a>

                            {!! Form::close() !!}
                        @endcan

                        @if ($client->is_enable_login == 1)
                        <a href="{{ route('users.login', \Crypt::encrypt($client->id)) }}"
                            class="dropdown-item">
                            <i class="ti ti-road-sign"></i>
                            <span class="text-danger"> {{ __('Login Disable') }}</span>
                        </a>
                    @elseif ($client->is_enable_login == 0 && $client->password == null)
                        <a href="#" data-url="{{ route('clients.reset', \Crypt::encrypt($client->id)) }}"
                            data-ajax-popup="true" data-size="md" class="dropdown-item login_enable"
                            data-title="{{ __('New Password') }}" class="dropdown-item">
                            <i class="ti ti-road-sign"></i>
                            <span class="text-success"> {{ __('Login Enable') }}</span>
                        </a>
                    @else
                        <a href="{{ route('users.login', \Crypt::encrypt($client->id)) }}"
                            class="dropdown-item">
                            <i class="ti ti-road-sign"></i>
                            <span class="text-success"> {{ __('Login Enable') }}</span>
                        </a>
                    @endif

                        <a href="#!" data-url="{{route('clients.reset',\Crypt::encrypt($client->id))}}" data-ajax-popup="true" class="dropdown-item" data-bs-original-title="{{__('Reset Password')}}">
                            <i class="ti ti-adjustments"></i>
                            <span>  {{__('Reset Password')}}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body full-card">
            <div class="img-fluid rounded-circle card-avatar">
                <img src="{{(!empty($client->avatar))? asset(Storage::url("uploads/avatar/".$client->avatar)): asset(Storage::url("uploads/avatar/avatar.png"))}}"  class="img-user wid-80 rounded-circle">
            </div>
            <h4 class="mt-2 text-primary">{{ $client->name }}</h4>
            <p></p>
            <div class="row">
                <div class="col-12 col-sm-12">
                    <div class="d-grid text-primary">
                        {{ $client->email }}
                    </div>
                </div>
            </div>
            <div class="align-items-center h6 mt-2" data-bs-toggle="tooltip" title="{{__('Last Login')}}">
                {{ (!empty($client->last_login_at)) ? $client->last_login_at : '' }}
            </div>
        </div>
        <div class="card-footer p-3">
            <div class="row">
                <div class="col-6">
                    <h6 class="mb-0"> @if($client->clientDeals)
                            {{$client->clientDeals->count()}}
                        @endif</h6>
                    <p class="text-muted text-sm mb-0">{{__('Deals')}}</p>
                </div>
                <div class="col-6">
                    <h6 class="mb-0">@if($client->clientProjects)
                            {{ $client->clientProjects->count() }}
                        @endif</h6>
                    <p class="text-muted text-sm mb-0">{{__('Projects')}}</p>
                </div>
            </div>
        </div>
    </div>
</div>