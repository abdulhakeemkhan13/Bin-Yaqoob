<tr data-row_id="{{$pipeline->id}}">
    <td>{{ $pipeline->name }}</td>
    <td class="Action">
        <span>

            @can('delete pipeline')
            @php
                // Get the count of pipelines directly
                $pipelineCount = \App\Models\Pipeline::where('created_by', '=', \Auth::user()->creatorId())->count();
            @endphp
            
            @if($pipelineCount > 1)
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['pipelines.destroy', $pipeline->id]]) !!}
                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                    {!! Form::close() !!}
                </div>
            @endif
        @endcan

            @can('edit pipeline')
                <div class="action-btn bg-info ms-2">
                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ URL::to('pipelines/'.$pipeline->id.'/edit') }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Pipeline')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
            @endcan
            
        </span>
    </td>
</tr>
