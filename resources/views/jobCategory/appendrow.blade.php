<tr data-row_id="{{$category->id}}">
    <td>{{ $category->title }}</td>
    <td>
        @can('edit job category')
            <div class="action-btn bg-primary ms-2">
                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('job-category.edit',$category->id) }}" data-ajax-popup="true" data-title="{{__('Edit Job Category')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endcan
        @can('delete job category')
            <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['job-category.destroy', $category->id],'id'=>'delete-form-'.$category->id]) !!}
                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
                {!! Form::close() !!}
            </div>
        @endcan

    </td>
</tr>