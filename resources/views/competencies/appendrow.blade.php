<tr data-row_id="{{$competency->id}}">
    <td>{{ $competency->name }}</td>
    <td>{{ !empty($competency->performance)?$competency->performance->name:'' }}</td>



    <td class="Action">
        <span>
            @can('edit document type')
                <div class="action-btn bg-primary ms-2">
                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('competencies/'.$competency->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Competencies')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                            <i class="ti ti-pencil text-white"></i>
                        </a>
                    </div>
            @endcan

            @can('Delete Competencies')
                <div class="action-btn bg-danger ms-2">
                    <form method="POST" action="{{ route('competencies.destroy', $competency->id) }}" id="delete-form-{{ $competency->id }}">
                        @csrf
                        @method('DELETE')
                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}">
                            <i class="ti ti-trash text-white"></i>
                        </a>
                    </form>
                        </div>
            @endcan

        </span>
    </td>
</tr>