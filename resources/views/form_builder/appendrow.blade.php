<tr data-row_id="{{$form_builder->id}}">
    <td>{{ $form_builder->name }}</td>
    <td>
        {{ $form_builder->response->count() }}
    </td>
    @if(\Auth::user()->type=='company' || \Auth::user()->type=='branch')
        <td class="text-end">


            <div class="action-btn bg-primary ms-2">
                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center cp_link" data-link="<iframe src='{{url('/form/'.$form_builder->code)}}' title='{{ $form_builder->name }}'></iframe>" data-bs-toggle="tooltip" title="{{__('Click to copy iframe link')}}"><i class="ti ti-frame text-white"></i></a>
            </div>

            <div class="action-btn bg-secondary ms-2">
                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('form.field.bind',$form_builder->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Convert into Lead Setting')}}" data-title="{{__('Convert into Lead Setting')}}">
                    <i class="ti ti-exchange text-white"></i>
                </a>
            </div>


            <div class="action-btn bg-primary ms-2">
                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center cp_link" data-link="{{url('/form/'.$form_builder->code)}}" data-bs-toggle="tooltip" title="{{__('Click to copy link')}}"><i class="ti ti-copy text-white"></i></a>
            </div>

            @can('manage form field')
                <div class="action-btn bg-secondary ms-2">
                    <a href="{{route('form_builder.show',$form_builder->id)}}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Form field')}}"><i class="ti ti-table text-white"></i></a>
                </div>
            @endcan

            @can('view form response')
                <div class="action-btn bg-warning ms-2">
                    <a href="{{route('form.response',$form_builder->id)}}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('View Response')}}"><i class="ti ti-eye text-white"></i></a>
                </div>
            @endcan
            @can('edit form builder')
                <div class="action-btn bg-info ms-2">
                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('form_builder.edit',$form_builder->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Form Builder Edit')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
            @endcan
            @can('delete form builder')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['form_builder.destroy', $form_builder->id],'id'=>'delete-form-'.$form_builder->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                    {!! Form::close() !!}
                </div>
            @endcan
        </td>
    @endif
</tr>
