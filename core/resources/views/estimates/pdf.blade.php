<div class="container">
    <div style="width:300px;height:150px;float:left;">
        @if($estimate->estimate_logo != '')
            <img src="{{ $estimate->estimate_logo }}" alt="logo" style="max-width:90%"/>
        @endif
    </div>
    <div class="text-right" style="width:300px;height:150px;float:right;">
        <div class="text-right"> <h2>{{trans('application.estimate')}}</h2></div>
        <table style="width: 100%">
            <tr>
                <td class="text-right" style="width: 40%">{{trans('application.reference')}}</td>
                <td class="text-right">{{ $estimate->estimate_no }}</td>
            </tr>
            <tr>
                <td class="text-right">{{trans('application.date')}}</td>
                <td class="text-right">{{ format_date($estimate->estimate_date) }}</td>
            </tr>
        </table>
    </div>
    <div style="clear: both"></div>
    <div class="col-md-12">
        <div class="from_address">
            <h4 class="invoice_title">{{trans('application.our_information')}}</h4><hr class="separator"/>
            @if($settings)
                <h4>{{ $settings->name }}</h4>
                {{ $settings->address1 ? $settings->address1.',' : '' }} {{ $settings->state ? $settings->state : '' }}<br/>
                {{ $settings->city ? $settings->city.',' : '' }} {{ $settings->postal_code ? $settings->postal_code.','  : ''  }}<br/>
                {{ $settings->country }}<br/>
                @if($settings->phone != '')
                    {{trans('application.phone')}}: {{ $settings->phone }}<br/>
                @endif
                @if($settings->email != '')
                    {{trans('application.email')}}: {{ $settings->email }}.
                @endif
            @endif
        </div>
        <div class="to_address">
            <h4 class="invoice_title">{{trans('application.estimate_to')}} </h4><hr class="separator"/>
            <h4>{{ $estimate->client->name }}</h4>
            {{ $estimate->client->address1 ? $estimate->client->address1.',' : '' }} {{ $estimate->client->state ? $estimate->client->state : '' }}<br/>
            {{ $estimate->client->city ? $estimate->client->city.',' : '' }} {{ $estimate->client->postal_code ? $estimate->client->postal_code.','  : ''  }}<br/>
            {{ $estimate->client->country }}<br/>
            @if($estimate->client->phone != '')
                {{trans('application.phone')}}: {{ $estimate->client->phone }}<br/>
            @endif
            @if($estimate->client->email != '')
                {{trans('application.email')}}: {{ $estimate->client->email }}.
            @endif
        </div>
    </div>
    <div style="clear: both"></div>
    <div class="col-md-12">
        <table class="table">
            <tr>
                <th style="width:100%;font-size:20px; text-align:center;color:#015882" colspan="5">
                   {{--  <span style="border:2px red solid; border-radius:10px;padding:5px"> --}}
                    <span style="text-decoration:underline; padding:5px">
                        {{$estimate->estimate_title}}
                    </span>
                </th>
            </tr>
            <tr style="margin-bottom:30px;background: #2e3e4e;color: #fff;" class="item_table_header">
                <th style="width:50%">{{trans('application.product')}}</th>
                <th style="width:10%" class="text-center">{{trans('application.quantity')}}</th>
                <th style="width:15%" class="text-right">{{trans('application.price')}}</th>
                <th style="width:10%" class="text-center">{{trans('application.tax')}}</th>
                <th style="width:15%" class="text-right">{{trans('application.total')}}</th>
            </tr>
            <tbody id="items">
            @foreach($estimate->items->sortBy('item_order') as $item)
                <tr class="items">
                    <td><b>{{ $item->item_name }}</b><br/>{!! htmlspecialchars_decode(nl2br(e($item->item_description)),ENT_QUOTES) !!}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ format_amount($item->price) }}</td>
                    <td class="text-center">{{ $item->tax ? $item->tax->value.'%' : '' }}</td>
                    <td class="text-right">{{ format_amount($item->itemTotal) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-md-12">
        <table class="table">
            <tbody>
            <tr>
                <th style="width:75%" class="text-right">{{trans('application.subtotal')}}</th>
                <td class="text-right">
                    <span id="subTotal">{{ format_amount($estimate->totals['subTotal']) }}</span>
                </td>
            </tr>
            <tr>
                <th class="text-right">{{trans('application.tax')}}</th>
                <td class="text-right">
                    <span id="taxTotal">{{ format_amount($estimate->totals['taxTotal']) }}</span>
                </td>
            </tr>

            <tr class="amount_due">
                <th class="text-right">{{trans('application.total')}}:</th>
                <td class="text-right">
                    <span id="grandTotal">{{ $estimate->currency.' '.format_amount($estimate->totals['grandTotal']) }}</span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="col-md-12">
        <h4><b>Amount in words</b><br>
            <span style="font-family: 'Arial Narrow', Arial, sans-serif; 12px">
                <?php
                $amount_in_word = '';
                //$amount_in_word = $currencyTransformer->toWords($invoice->totals['amountDue']*100, 'NGN');
                $actual_amt = $estimate->totals['grandTotal'];
                $converted_amt = NumConvert::word($actual_amt);
                if (strpos($converted_amt, 'point') !== false) {
                    
                    $stop = strpos($converted_amt, 'point');
                    $amount_in_word = substr($converted_amt, 0,  $stop);
                    $amount_in_word = $amount_in_word . " naira "; 
                    //getting decimal point
                    $decimal_pos = strpos($actual_amt, '.');
                    //echo "act val = $actual_amt..string pos = $decimal_pos<br> ";
                    $decimal  = substr($actual_amt, $decimal_pos+1);
                    //echo "I am decimal = $decimal<br> ";
                    if(substr($decimal,1,-1) != "0"){
                        $amount_in_word = $amount_in_word . NumConvert::word($decimal) . " kobo";
                    }
                    else{
                        $amount_in_word = $amount_in_word . NumConvert::word($decimal*10) . " kobo";
                    }

                   
                }
                else{
                    $amount_in_word = $converted_amt . " naira only";
                }
                ?>

                <i>{{ ucfirst($amount_in_word)}}</i>
            </span>
        </h4> 
    </div>
    <div class="col-md-12">
        @if($estimate->notes)
            <h4 class="invoice_title">{{trans('application.notes')}}</h4><hr class="separator"/>
            {!! htmlspecialchars_decode($estimate->notes, ENT_QUOTES) !!}<br/><br/>
        @endif
        @if($estimate->terms)
            <h4 class="invoice_title">{{trans('application.terms')}}</h4><hr class="separator"/>
            {!! htmlspecialchars_decode($estimate->terms, ENT_QUOTES) !!}
        @endif
    </div>
</div>
<style>
    body {font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;overflow-x: hidden;overflow-y: auto;font-size: 13px;}
    .amount_due {font-size: 16px;font-weight: bold;}
    .invoice_title{color: #2e3e4e;font-weight: bold;}
    .text-right{text-align: right;}
    .text-center{text-align: center;}
    .col-sm-12{width: 100%;}
    .col-sm-6{width: 50%;float: left;}
    table {border-spacing: 0;border-collapse: collapse;}
    .table {width: 100%;max-width: 100%;margin-bottom: 20px;}
    .item_table_header>th{padding: 8px;line-height: 1.42857143;vertical-align: top;}
    .table>tr>td, .table>tr>th{padding: 8px;line-height: 1.42857143;vertical-align: top;}
    hr.separator{border-color:  #2e3e4e;margin-top: 10px;margin-bottom: 10px;}
    tbody#items>tr>td{border: 3px solid #fff !important;vertical-align: middle;padding: 8px;}
    #items{background-color: #f1f1f1;}
    .form-group {margin-bottom: 1rem;}
    .from_address{width: 330px;height:200px;margin-bottom:1rem;float: left;}
    .to_address{width: 330px;height:200px;float: right;}
    .capitalize{text-transform: uppercase}
</style>