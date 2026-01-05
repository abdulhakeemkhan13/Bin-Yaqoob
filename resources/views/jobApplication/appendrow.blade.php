<tr data-row_id="{{$jobBoard->id}}">
    <td>{{ !empty($jobBoard->applications)?$jobBoard->applications->name:'-' }}</td>
    <td>{{!empty($jobBoard->applications)?!empty($jobBoard->applications->jobs)?$jobBoard->applications->jobs->title:'-':'-'}}</td>
    <td>{{!empty($jobBoard->applications)?!empty($jobBoard->applications->jobs)?!empty($jobBoard->applications->jobs)?!empty($jobBoard->applications->jobs->branches)?$jobBoard->applications->jobs->branches->name:'-':'-':'-':'-'}}</td>
    <td>{{\Auth::user()->dateFormat(!empty($jobBoard->applications)?$jobBoard->applications->created_at:'-' )}}</td>
    <td>{{\Auth::user()->dateFormat($jobBoard->joining_date)}}</td>
    <td>
        @if($jobBoard->status=='pending')
            <span class="badge bg-warning p-2 px-3 rounded">{{\App\Models\JobOnBoard::$status[$jobBoard->status]}}</span>
        @elseif($jobBoard->status=='cancel')
            <span class="badge bg-danger p-2 px-3 rounded">{{\App\Models\JobOnBoard::$status[$jobBoard->status]}}</span>
        @else
            <span class="badge bg-primary p-2 px-3 rounded">{{\App\Models\JobOnBoard::$status[$jobBoard->status]}}</span>
        @endif
    </td>

    <td>
        @if($jobBoard->status=='confirm' && $jobBoard->convert_to_employee==0)
        <div class="action-btn bg-warning ms-2">
            {!! Form::open(['method' => 'get', 'route' => ['job.on.board.convert', $jobBoard->id],'id'=>'job-form-'.$jobBoard->id]) !!}
            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip"
               data-original-title="{{__('Convert to Employee')}}" title="{{__('Convert to Employee')}}"
               data-confirm="You want to confirm convert to invoice. Press Yes to continue or Cancel to go back"
               data-confirm-yes="document.getElementById('job-form-{{$jobBoard->id}}').submit();">
                <i class="ti ti-exchange text-white"></i>
            </a>
            {!! Form::close() !!}
        </div>
        @elseif($jobBoard->status=='confirm' && $jobBoard->convert_to_employee!=0)
        <div class="action-btn bg-info ms-2">
            <a href="{{route('employee.show', \Crypt::encrypt($jobBoard->convert_to_employee))}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('View')}}" data-original-title="{{__('Employee Detail')}}"><i class="ti ti-eye text-white"></i></a>
        </div>
        @endif

        <div class="action-btn bg-primary ms-2">
            <a href="#" data-url="{{route('job.on.board.edit', $jobBoard->id)}}" data-ajax-popup="true" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
        </div>

        <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['job.on.board.delete', $jobBoard->id],'id'=>'delete-form-'.$jobBoard->id]) !!}
            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$jobBoard->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
            {!! Form::close() !!}
        </div>

        @if ($jobBoard->status == 'confirm')
            <div class="action-btn bg-secondary ms-2">
                <a href="{{route('offerlatter.download.pdf',$jobBoard->id)}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('OfferLetter PDF')}}" target="_blanks"><i class="ti ti-download text-white"></i></a>
            </div>
            <div class="action-btn bg-secondary ms-2">
                <a href="{{route('offerlatter.download.doc',$jobBoard->id)}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('OfferLetter DOC')}}" target="_blanks"><i class="ti ti-download text-white"></i></a>
            </div>
        @endif
    </td>

</tr>
