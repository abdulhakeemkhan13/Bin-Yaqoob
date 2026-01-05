@extends('layouts.admin')
@section('page-title')
    {{__('Manage Designation')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Designation')}}</li>
@endsection


@section('action-btn')
    <div class="float-end">
        @can('create designation')
            <a href="#" data-url="{{ route('designation.create') }}" data-ajax-popup="true" data-title="{{__('Create New Designation')}}" data-bs-toggle="tooltip" title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable" id="designation-table">
                            <thead>
                            <tr>
                                <th>{{ __('Branch') }}</th> 
                                <th>{{__('Department')}}</th>
                                <th>{{__('Designation')}}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody class="font-style" id="designation-table-body">
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
        'Senior Executive Finance',
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script-page')
    <script>
        $(document).on('change', '#branch_id', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(branch_id) {
            var data = {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            }

            $.ajax({
                url: '{{ route('employee.getdepartment') }}',
                method: 'POST',
                data: data,
                success: function(data) {
                    $('#department_id').empty();
                    $('#department_id').append(
                        '<option value="" disabled>{{ __('Select Department') }}</option>');

                    $.each(data, function(key, value) {
                        $('#department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    $('#department_id').val('');
                }
            });
        }
    </script>
@endpush