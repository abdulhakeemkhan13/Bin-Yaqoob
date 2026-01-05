<div class="col-md-3 mb-4" data-row_id="{{$user->id}}">
    <div class="card text-center card-2" data-id="{{$user->id}}">
        <div class="card-header border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    @if (\Auth::user()->type == 'super admin')
                        <div class="badge bg-primary p-2 px-3 rounded">
                            {{ !empty($user->currentPlan) ? $user->currentPlan->name : '' }}
                        </div>
                    @else
                        <div class="badge bg-primary p-2 px-3 rounded">
                            {{ ucfirst($user->type) }}
                        </div>
                    @endif
                </h6>
            </div>
            @if (Gate::check('edit user') || Gate::check('delete user'))
                <div class="card-header-right">
                    <div class="btn-group card-option">
                        @if ($user->is_active == 1 && $user->is_disable == 1)
                            <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="ti ti-dots-vertical"></i>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end">

                                @can('edit user')
                                    <a href="#!" data-size="lg"
                                        data-url="{{ route('branches.edit', $user->id) }}"
                                        data-ajax-popup="true" class="dropdown-item"
                                        data-bs-original-title="{{ \Auth::user()->type == 'company' ?  __('Edit Branch')  : __('Edit User') }}">
                                        <i class="ti ti-pencil"></i>
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                @endcan

                                {{-- @can('delete user')
                                    {!! Form::open([
                                        'method' => 'DELETE',
                                        'route' => ['branches.destroy', $user['id']],
                                        'id' => 'delete-form-' . $user['id'],
                                    ]) !!}
                                    <a href="#!" class="dropdown-item bs-pass-para">
                                        <i class="ti ti-archive"></i>
                                        <span>
                                            @if ($user->delete_status != 0)
                                                {{ __('Delete') }}
                                            @else
                                                {{ __('Restore') }}
                                            @endif
                                        </span>
                                    </a>
                                    {!! Form::close() !!}
                                @endcan --}}
                            </div>
                        @else
                            {{-- <a href="#" class="action-item text-lg"><i class="ti ti-lock"></i></a> --}}
                        @endif

                    </div>
                </div>
            @endif
        </div>

        <div class="card-body full-card">
            <div class="img-fluid rounded-circle card-avatar">
                <img src="{{ !empty($user->avatar) ? asset(Storage::url('uploads/avatar/' . $user->avatar)) : asset(Storage::url('uploads/avatar/avatar.png')) }}"
                    class="img-user wid-80 round-img rounded-circle">
            </div>
            <h4 class=" mt-3 text-primary">{{ $user->name }}</h4>
            @if ($user->delete_status == 0)
                {{-- <h5 class="office-time mb-0">{{ __('Soft Deleted') }}</h5> --}}
            @endif
            <small class="text-primary">{{ $user->email }}</small>
            <p></p>
            <div class="text-center" data-bs-toggle="tooltip" title="{{ __('Last Login') }}">
                {{ !empty($user->last_login_at) ? $user->last_login_at : '' }}
            </div>
        </div>
    </div>
</div>