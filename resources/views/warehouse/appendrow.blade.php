<tr class="font-style" data-row_id="{{$warehouse->id}}">
    <td>{{ $warehouse->name}}</td>
    <td>{{ $warehouse->address }}</td>
    <td>{{ $warehouse->city }}</td>
    <td>{{ $warehouse->city_zip }}</td>

    @if(Gate::check('show warehouse') || Gate::check('edit warehouse') || Gate::check('delete warehouse'))
        <td class="Action">
            @can('show warehouse')
                <div class="action-btn bg-warning ms-2">

                    <a href="{{ route('warehouse.show',$warehouse->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                       data-bs-toggle="tooltip" title="{{__('View')}}"><i class="ti ti-eye text-white"></i></a>

                </div>
            @endcan
            @can('edit warehouse')
                <div class="action-btn bg-info ms-2">
                    <a href="#" class="mx-3 btn btn-sm  align-items-center" data-url="{{ route('warehouse.edit',$warehouse->id) }}" data-ajax-popup="true"  data-size="lg " data-bs-toggle="tooltip" title="{{__('Edit')}}"  data-title="{{__('Edit Warehouse')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
            @endcan
            @can('delete warehouse')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['warehouse.destroy', $warehouse->id],'id'=>'delete-form-'.$warehouse->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" ><i class="ti ti-trash text-white"></i></a>
                    {!! Form::close() !!}
                </div>
            @endcan
        </td>
    @endif
</tr>