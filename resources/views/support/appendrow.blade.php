<tr data-row_id="{{$support->id}}">
    <td scope="row">
        <div class="media align-items-center">
            <div>
                <div class="avatar-parent-child">
                    <img alt="" class="avatar rounded-circle avatar-sm me-1" @if(!empty($support->createdBy) && !empty($support->createdBy->avatar) && file_exists('storage/uploads/avatar/'.$support->createdBy->avatar)) src="{{asset(Storage::url('uploads/avatar')).'/'.$support->createdBy->avatar}}" @else  src="{{asset(Storage::url('uploads/avatar')).'/avatar.png'}}" @endif>
                    @if($support->replyUnread()>0)
                        <span class="avatar-child avatar-badge bg-success"></span>
                    @endif
                </div>
            </div>
            <div class="media-body">
                {{!empty($support->createdBy)?$support->createdBy->name:''}}
            </div>
        </div>
    </td>
    <td scope="row">
        <div class="media align-items-center">
            <div class="media-body">
                <a href="{{ route('support.reply',\Crypt::encrypt($support->id)) }}" class="name h6 mb-0 text-sm">{{$support->subject}}</a><br>
                @if($support->priority == 0)
                    <span data-toggle="tooltip" data-title="{{__('Priority')}}" class="text-capitalize badge bg-primary p-2 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                @elseif($support->priority == 1)
                    <span data-toggle="tooltip" data-title="{{__('Priority')}}" class="text-capitalize badge bg-info p-2 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                @elseif($support->priority == 2)
                    <span data-toggle="tooltip" data-title="{{__('Priority')}}" class="text-capitalize badge bg-warning p-2 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                @elseif($support->priority == 3)
                    <span data-toggle="tooltip" data-title="{{__('Priority')}}" class="text-capitalize badge bg-danger p-2 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                @endif
            </div>
        </div>
    </td>
    <td>{{$support->ticket_code}}</td>
    <td>
        @if(!empty($support->attachment))
            <a  class="action-btn bg-primary ms-2 btn btn-sm align-items-center" href="{{ $supportpath . '/' . $support->attachment }}" download=""  data-bs-toggle="tooltip" title="{{__('Download')}}" target="_blank">
                <i class="ti ti-download text-white"></i>
            </a>
            <a href="{{ $supportpath . '/' . $support->attachment }}"
               class="action-btn bg-secondary ms-2 mx-3 btn btn-sm align-items-center">
                <span class="btn-inner--icon"><i class="ti ti-crosshair text-white" ></i></span>
            </a>
        @else
            -
        @endif

    </td>
    <td>{{!empty($support->assignUser)?$support->assignUser->name:'-'}}</td>
    
    <td>
        @if($support->status == 'Open')
            <span class="status_badge text-capitalize badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Support::$status[$support->status]) }}</span>
        @elseif($support->status == 'Close')
            <span class="status_badge text-capitalize badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Support::$status[$support->status]) }}</span>
        @elseif($support->status == 'On Hold')
            <span  class="status_badge text-capitalize badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Support::$status[$support->status]) }}</span>
        @endif
    </td>
    <td>{{\Auth::user()->dateFormat($support->created_at)}}</td>
    <td class="Action">
    <span>
        <div class="action-btn bg-warning ms-2">
            <a href="{{ route('support.reply',\Crypt::encrypt($support->id)) }}" data-title="{{__('Support Reply')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Reply')}}" data-original-title="{{__('Reply')}}">
                <i class="ti ti-corner-up-left text-white"></i>
            </a>
        </div>
        @if(\Auth::user()->type=='company' || \Auth::user()->id==$support->ticket_created)
            <div class="action-btn bg-primary ms-2">
                <a href="#" data-size="lg" data-url="{{ route('support.edit',$support->id) }}" data-ajax-popup="true" data-title="{{__('Edit Support')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
            <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['support.destroy', $support->id],'id'=>'delete-form-'.$support->id]) !!}
                    <a href="#!" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?" title="{{__('Delete')}}" data-confirm-yes="document.getElementById('delete-form-{{$support->id}}').submit();">
                        <i class="ti ti-trash text-white"></i>
                    </a>
                 {!! Form::close() !!}
            </div>

        @endif
    </span>
    </td>
</tr>