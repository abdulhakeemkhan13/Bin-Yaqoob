<li class="d-flex align-items-center justify-content-between list-group-item" data-id="{{$stage->id}}" data-row-id="{{$stage->id}}">
    <h6 class="mb-0">
        <i class="me-3 ti ti-arrows-maximize " data-feather="move"></i>
        <span>{{$stage->title}}</span>
    </h6>
    <span class="float-end">
        @can('edit job stage')
            <div class="action-btn bg-primary ms-2">
                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('job-stage.edit',$stage->id) }}" data-ajax-popup="true" data-title="{{__('Edit Job Stage')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endcan
        @can('delete job stage')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['job-stage.destroy', $stage->id],'id'=>'delete-form-'.$stage->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
                    {!! Form::close() !!}
                 </div>
                @endcan
        </span>
</li>