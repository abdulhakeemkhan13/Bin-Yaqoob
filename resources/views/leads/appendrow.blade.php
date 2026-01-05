<div class="card" data-id="{{ $lead->id }}" data-row_id="{{ $lead->id }}">
    <div class="pt-3 ps-3">
        @php
            // Check if labels method exists and handle potential undefined property
            try {
                $labels = $lead->labels();
            } catch (\Exception $e) {
                $labels = collect();
            }
        @endphp
        @if ($labels->count())
            @foreach ($labels as $label)
                <div class="badge-xs badge bg-{{ $label->color }} p-2 px-3 rounded">
                    {{ $label->name }}
                </div>
            @endforeach
        @endif
        
        @if ($lead->updated_at < \Carbon\Carbon::now()->subHours(96))
            <div class="badge-xs badge bg-primary p-0 rounded float-end" style="margin: 4px 12px 0px 0px;">
                <a href="#" data-id="{{ $lead->id }}" data-type="Follow up" data-bs-toggle="tooltip"
                   title="{{ __('No Activity for the Last 96 Hours') }}" class="btn btn-sm btn-primary rounded">
                    Follow up
                </a>
            </div>
        @endif
    </div>
    <div class="card-header border-0 pb-0 position-relative">
        <h5><a
                href="@can('view lead')@if ($lead->is_active){{ route('leads.show', $lead->id) }}@else#@endif @else#@endcan">{{ $lead->name }}</a>
        </h5>
        <div class="card-header-right">
            @if (Auth::user()->type != 'client')
                <div class="btn-group card-option">
                    <button type="button" class="btn dropdown-toggle"
                        data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="ti ti-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        @can('edit lead')
                            <a href="#!" data-size="md"
                                data-url="{{ URL::to('leads/' . $lead->id . '/labels') }}"
                                data-ajax-popup="true" class="dropdown-item"
                                data-bs-original-title="{{ __('Add Labels') }}">
                                <i class="ti ti-bookmark"></i>
                                <span>{{ __('Labels') }}</span>
                            </a>
                            <a href="#!" data-size="sm"
                                data-url="{{ URL::to('leads/' . $lead->id . '/stage') }}"
                                data-ajax-popup="true"
                                data-bs-original-title="{{ __('Change Stage of this lead') }}"
                                class="dropdown-item">
                                <i class="ti ti-arrow-right"></i>
                                <span>{{ __('Move Stage') }}</span>
                            </a>
                            <a href="#!" data-size="lg"
                                data-url="{{ URL::to('leads/' . $lead->id . '/edit') }}"
                                data-ajax-popup="true" class="dropdown-item"
                                data-bs-original-title="{{ __('Edit Lead') }}">
                                <i class="ti ti-pencil"></i>
                                <span>{{ __('Edit') }}</span>
                            </a>
                        @endcan
                        @can('delete lead')
                            {!! Form::open([
                                'method' => 'DELETE',
                                'route' => ['leads.destroy', $lead->id],
                                'id' => 'delete-form-' . $lead->id,
                            ]) !!}
                            <a href="#!" class="dropdown-item bs-pass-para">
                                <i class="ti ti-archive"></i>
                                <span> {{ __('Delete') }} </span>
                            </a>
                            {!! Form::close() !!}
                        @endcan


                    </div>
                </div>
            @endif
        </div>
    </div>
    <?php
    try {
        $products = $lead->products();
    } catch (\Exception $e) {
        $products = collect();
    }

    try {
        $sources = $lead->sources();
    } catch (\Exception $e) {
        $sources = collect();
    }
    ?>
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            <ul class="list-inline mb-0">

                <li class="list-inline-item d-inline-flex align-items-center"
                    data-bs-toggle="tooltip" title="{{ __('Product') }}">
                    <i class="f-16 text-primary ti ti-shopping-cart"></i>
                    {{ count($products) }}
                </li>

                <li class="list-inline-item d-inline-flex align-items-center"
                    data-bs-toggle="tooltip" title="{{ __('Source') }}">
                    <i
                        class="f-16 text-primary ti ti-social"></i>{{ count($sources) }}
                </li>
            </ul>
            <div class="user-group">
                @foreach ($lead->users as $user)
                    <img src="@if ($user->avatar) {{ asset('/storage/uploads/avatar/' . $user->avatar) }} @else {{ asset('storage/uploads/avatar/avatar.png') }} @endif"
                        alt="image" data-bs-toggle="tooltip"
                        title="{{ $user->name }}">
                @endforeach
            </div>
        </div>
    </div>
</div>
