@extends('layouts.admin')

@section('page-title')
    {{__('Manage Labels')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Labels')}}</li>
@endsection

@section('action-btn')
    @can('create label')
        <div class="float-end">
            <a href="#" data-size="md" data-url="{{ route('labels.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create Labels')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        </div>
    @endcan
@endsection

@section('content')

    <div class="row">
        <div class="col-md-3">
            @include('layouts.crm_setup')
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                        @php($i=0)
                        @if($pipelines)
                        @foreach($pipelines as $key => $pipeline)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if($i==0) active @endif" id="pills-user-tab-{{$key}}" data-bs-toggle="pill"
                                        data-bs-target="#tab{{$key}}" type="button">{{$pipeline['name']}}
                                </button>
                            </li>
                            @php($i++)
                        @endforeach
                        @else
                            <button class="nav-link" type="button">Create Pipeline First.</button>
                        @endif
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="tab-content" id="pills-tabContent">
                        @php($i=0)
                        @foreach($pipelines as $key => $pipeline)
                            <div class="tab-pane fade show @if($i==0) active @endif" id="tab{{$key}}" role="tabpanel" aria-labelledby="pills-user-tab-{{$key}}">
                                <div class="table-responsive">
                                    <table class="table datatable" id="label-table">
                                        <thead>
                                            <tr>
                                                <th>{{__('Name')}}</th>
                                                <th width="200px">{{__('Action')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="label-table-body">
                                            @foreach ($pipeline['labels'] as $label)
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
                                                            @if(count($pipeline['labels']))
                                                                @can('delete label')
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['labels.destroy', $label->id]]) !!}
                                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            @endif
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @php($i++)
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
