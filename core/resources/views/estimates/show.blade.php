@extends('app')
@section('content')
<div class="col-md-12 content-header" >
    <h1><i class="fa fa-quote-left"></i> {{trans('application.estimates')}}</h1>
</div>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                <div class="row">
                    <div class="col-md-12" style="max-width:1040px;">
                        @if (Session::has('flash_notification.message'))
                            {!! message() !!}
                        @endif
                        <a href="{{ route('estimates.index') }}" class="btn btn-info btn-xs"> <i class="fa fa-chevron-left"></i> {{trans('application.back')}}</a>
                        @if(hasPermission('send_estimate'))
                            <a href="{{route('estimate_send_modal',$estimate->uuid)}}" data-toggle="ajax-modal" class="btn btn-success btn-xs pull-right" style="margin-left: 5px"> <i class="fa fa-send"></i> {{trans('application.send')}}</a>
                        @endif
                            <a href="{{ url('estimates/pdf', $estimate->uuid) }}" class="btn btn-primary btn-xs pull-right" style="margin-left: 5px"> <i class="fa fa-download"></i> {{trans('application.download')}}</a>
                        @if(hasPermission('edit_estimate'))
                            <a href="{{ route('estimates.edit', $estimate->uuid) }}" class="btn btn-warning btn-xs pull-right" > <i class="fa fa-pencil"></i> {{trans('application.edit')}}</a>
                        @endif
                    </div>
                </div>
                <div class="row">
                        <div class="invoice">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="panel-body">
                                        @if($estimate_settings && $estimate_settings->logo != '')
                                            <img src="{{image_url($estimate_settings->logo)}}" alt="logo" width="100%"/>
                                        @endif
                                     </div>
                                </div>
                                <div class="col-md-8 text-right">
                                    <div class="panel-body" style="font-size: 16px;font-weight: bold;padding: 0">
                                        <div class="col-xs-12 text-right"> <h1 style="margin: 0">{{trans('application.estimate')}}</h1></div>
                                        <div class="col-xs-9 text-right invoice_title">{{trans('application.reference')}}</div>
                                        <div class="col-xs-3 text-right">{{ $estimate->estimate_no }}</div>
                                        <div class="col-xs-9 text-right invoice_title">{{trans('application.date')}}</div>
                                        <div class="col-xs-3 text-right">{{ format_date($estimate->estimate_date) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                        <div class="panel-body">
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
                                </div>
                                <div class="col-xs-6">
                                        <div class="panel-body">
                                            <h4 class="invoice_title">{{trans('application.billing_to')}} </h4><hr class="separator"/>
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
                            </div>
                            <div class="col-md-12">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width:100%;font-size:20px; text-align:center;color:#015882" colspan="5">
                                           {{--  <span style="border:2px red solid; border-radius:10px;padding:5px"> --}}
                                            <span style="text-decoration:underline; padding:5px">
                                                {{$estimate->estimate_title}}
                                            </span>
                                        </th>
                                    </tr>
                                    <tr style="margin-bottom:30px;background: #2e3e4e;color: #fff;">
                                    <th style="width:50%">{{trans('application.product')}}</th>
                                    <th style="width:10%" class="text-center">{{trans('application.quantity')}}</th>
                                    <th style="width:15%" class="text-right">{{trans('application.price')}}</th>
                                    <th style="width:10%" class="text-center">{{trans('application.tax')}}</th>
                                    <th style="width:15%" class="text-right">{{trans('application.total')}}</th>
                                </tr>
                                </thead>
                                <tbody id="items">
                                @foreach($estimate->items->sortBy('item_order') as $item)
                                <tr>
                                    <td><b>{!! $item->item_name !!}</b><br/>{!! $item->item_description !!}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">{{ format_amount($item->price) }}</td>
                                    <td class="text-center">{{ $item->tax ? $item->tax->value.'%' : '' }}</td>
                                    <td class="text-right">{{ format_amount($item->itemTotal) }}</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                            </div><!-- /.col -->
                            <div class="col-md-6"></div><!-- /.col -->
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                        <tr>
                                            <th style="width:50%">{{trans('application.subtotal')}}</th>
                                            <td class="text-right">
                                                <span id="subTotal">{{ format_amount($estimate->totals['subTotal']) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{trans('application.tax')}}</th>
                                            <td class="text-right">
                                                <span id="taxTotal">{{ format_amount($estimate->totals['taxTotal']) }}</span>
                                            </td>
                                        </tr>

                                        <tr class="amount_due">
                                            <th>{{trans('application.total')}}</th>
                                            <td class="text-right">
                                                <span class="currencySymbol" style="display: inline-block;">{{ $estimate->currency }} </span>
                                                <span id="grandTotal">{{format_amount($estimate->totals['grandTotal'])}}</span>
                                            </td>
                                        </tr>
                                        </tbody></table>
                                </div>
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
                                            $amount_in_word = $converted_amt . " naira ";
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
                    </div>
                </div>
            </div>
        </div>
        <div class="cleafix"></div>
    </div>
</section>
<style>
    .invoice_title{
        color: #2e3e4e;
        font-weight: bold;
    }
    hr.separator{
        border-color:  #2e3e4e;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    tbody#items > tr > td{
        border: 3px solid #fff !important;
        vertical-align: middle;
    }
    #items{
        background-color: #f1f1f1;
    }
</style>
@endsection





