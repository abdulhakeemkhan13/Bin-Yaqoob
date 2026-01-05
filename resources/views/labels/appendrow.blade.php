<tr data-row_id="{{$label->id}}">
    <td>{{$label->name}}</td>
    <td class="Action">
        <span>
            @can('edit label')
                <div class="action-btn bg-info ms-2">
                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ URL::to('labels/'.$label->id.'/edit') }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Labels')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
            @endcan
            @php
                // Get the pipeline directly from the label
                $labelPipeline = $label->pipeline;
                $hasLabels = $labelPipeline && $labelPipeline->labels && count($labelPipeline->labels) > 0;
            @endphp
            {{-- @if($hasLabels) --}}
                @can('delete label')
                    <div class="action-btn bg-danger ms-2">
                        {!! Form::open(['method' => 'DELETE', 'route' => ['labels.destroy', $label->id]]) !!}
                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                        {!! Form::close() !!}
                    </div>
                @endcan
            {{-- @endif --}}
        </span>
    </td>
</tr>
