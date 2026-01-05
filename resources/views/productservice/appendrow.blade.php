<tr class="font-style" data-row_id="{{$productService->id}}">
    <td>{{ $productService->name}}</td>
    <td>{{ $productService->sku }}</td>
    <td>{{ \Auth::user()->priceFormat($productService->sale_price) }}</td>
    <td>{{  \Auth::user()->priceFormat($productService->purchase_price )}}</td>
    <td>
        @if (!empty($productService->tax_id))
            @php
                $itemTaxes = [];
                $getTaxData = Utility::getTaxData();

                    foreach (explode(',', $productService->tax_id) as $tax) {
                        $itemTax['name'] = $getTaxData[$tax]['name'];
                        $itemTax['rate'] = $getTaxData[$tax]['rate'] . '%';

                        $itemTaxes[] = $itemTax;

                    }
                    $productService->itemTax = $itemTaxes;
            @endphp
            @foreach ($productService->itemTax as $tax)

                        <span>{{$tax['name'] .' ('.$tax['rate'] .')'}}</span><br>
            @endforeach
    @else
        -
    @endif
    </td>
    <td>{{ !empty($productService->category)?$productService->category->name:'' }}</td>
    <td>{{ !empty($productService->unit)?$productService->unit->name:'' }}</td>
    @if($productService->type == 'product')
        <td>{{$productService->quantity}}</td>
    @else
        <td>-</td>
    @endif
    <td>{{ucwords($productService->type)}}</td>

    @if(Gate::check('edit product & service') || Gate::check('delete product & service'))
        <td class="Action">
            <div class="action-btn bg-warning ms-2">
                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('productservice.detail',$productService->id) }}"
                   data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Warehouse Details')}}" data-title="{{__('Warehouse Details')}}">
                    <i class="ti ti-eye text-white"></i>
                </a>
            </div>

            @can('edit product & service')
                <div class="action-btn bg-info ms-2">
                    <a href="#" class="mx-3 btn btn-sm  align-items-center" data-url="{{ route('productservice.edit',$productService->id) }}" data-ajax-popup="true"  data-size="lg " data-bs-toggle="tooltip" title="{{__('Edit')}}"  data-title="{{__('Edit Product')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
            @endcan
            @can('delete product & service')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['productservice.destroy', $productService->id],'id'=>'delete-form-'.$productService->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" ><i class="ti ti-trash text-white"></i></a>
                    {!! Form::close() !!}
                </div>
            @endcan
        </td>
    @endif
</tr>