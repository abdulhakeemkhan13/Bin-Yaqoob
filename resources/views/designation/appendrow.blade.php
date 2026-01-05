@foreach ($designations as $designation)
@php
    $department = \App\Models\Department::where('id', $designation->department_id)->first();
@endphp
<tr data-row_id="{{$designation->id}}">
    <td>{{ !empty(@$designation->branch_id) ? @$designation->branch->name : '-' }}</td>
    <td>{{ !empty(@$department->name)?@$department->name:'' }}</td>
    <td>{{ @$designation->name }}</td>

    @php
    $isProtected = in_array($designation->name, [
        'General Manager Finance',
        'COO',
        'CEO',
        'Chairman',
    ]);
@endphp

    <td class="Action">
        <span>

            @if(!$isProtected)
    @can('edit designation')
        <div class="action-btn bg-primary ms-2">
            <a href="#" class="mx-3 btn btn-sm align-items-center" 
               data-url="{{route('designation.edit',$designation->id)}}"
               data-ajax-popup="true" data-title="{{__('Edit Designation')}}">
                <i class="ti ti-pencil text-white"></i>
            </a>
        </div>
    @endcan
@else
    <div class="action-btn ms-2 opacity-50">
        <a class="mx-3 btn btn-sm disabled">
            <i class="ti ti-pencil text-white"></i>
        </a>
    </div>
@endif


            @if(!$isProtected)
    @can('delete designation')
        <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['designation.destroy', $designation->id],'id'=>'delete-form-'.$designation->id]) !!}
                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para">
                    <i class="ti ti-trash text-white"></i>
                </a>
            {!! Form::close() !!}
        </div>
    @endcan
@else
    <div class="action-btn ms-2 opacity-50">
        <a class="mx-3 btn btn-sm disabled">
            <i class="ti ti-trash text-white"></i>
        </a>
    </div>
@endif


        </span>
    </td>
</tr>
@endforeach