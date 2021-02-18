<?php 
use NumberToWords\NumberToWords; 
// create the number to words "manager" class
$numberToWords = new NumberToWords();

// build a new currency transformer using the RFC 3066 language identifier
$currencyTransformer = $numberToWords->getCurrencyTransformer('en');
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head><body>
<div class="container">
    <div style="width:300px;height:150px;float:left;overflow: hidden">
        @if($invoice->pdf_logo != '')
            <img src="{{ $invoice->pdf_logo }}" alt="logo" style="max-width: 90%"/>
        @endif
        <div style="font-size:10px; text-align:center"><b>Generators || Transformer || Inverters
            || UPS || ELV Panels <br>   Distribution Boards  &amp; Installation Accessories</b></div> </div>
    <div class="text-right" style="width:300px;height:130px;float:right;">
        <div class="text-right"> <h2>{{trans('application.invoice')}}</h2></div>
        <table style="width: 100%">
            <tr>
                <td class="text-right" style="width: 40%">{{trans('application.reference')}}</td>
                <td class="text-right">{{ $invoice->number }}</td>
            </tr>
            <tr>
                <td class="text-right">{{trans('application.date')}}</td>
                <td class="text-right">{{ format_date($invoice->invoice_date) }}</td>
            </tr>
            <tr>
                <td class="text-right">{{trans('application.due_date')}}</td>
                <td class="text-right">{{ format_date($invoice->due_date) }}</td>
            </tr>
            @if($settings && $settings->vat != '')
                <tr>
                    <td class="text-right">{{trans('application.vat_number')}}</td>
                    <td class="text-right">{{ $settings ? $settings->vat : '' }}</td>
                </tr>
            @endif
        </table>
    </div>
    <div style="clear: both"></div>
    <div class="col-md-12 form-group">
        <div class="from_address">
            <h4 class="invoice_title">{{trans('application.our_information')}}</h4><hr class="separator"/>
            @if($settings)
                <h4>{{ $settings->name }}</h4>
                @if($settings->address1 != '' || $settings->state != '')
                    {{ $settings->address1 ? $settings->address1.',' : '' }} {{ $settings->state ? $settings->state : '' }}<br/>
                @endif
                @if($settings->city != '' || $settings->postal_code != '')
                    {{ $settings->city ? $settings->city.',' : '' }} {{ $settings->postal_code ? $settings->postal_code.','  : ''  }}<br/>
                @endif
                @if($settings->country != '')
                    {{ $settings->country }}<br/>
                @endif
                @if($settings->phone != '')
                    {{trans('application.phone')}}: {{ $settings->phone }}<br/>
                @endif
                @if($settings->email != '')
                    {{trans('application.email')}}: {{ $settings->email }}.
                @endif
            @endif
        </div>
        <div class="to_address">
            <h4 class="invoice_title">{{trans('application.billing_to')}} </h4><hr class="separator"/>
            <h4>{{ $invoice->client->name }}</h4>
            @if($invoice->client->address1 != '' || $invoice->client->state != '')
                {{ $invoice->client->address1 ? $invoice->client->address1.',' : '' }} {{ $invoice->client->state ? $invoice->client->state : '' }}<br/>
            @endif
            @if($invoice->client->city != '' || $invoice->client->postal_code != '')
                {{ $invoice->client->city ? $invoice->client->city.',' : '' }} {{ $invoice->client->postal_code ? $invoice->client->postal_code.','  : ''  }}<br/>
            @endif
            @if($invoice->client->country != '')
                {{ $invoice->client->country }}<br/>
            @endif
            @if($invoice->client->phone != '')
                {{trans('application.phone')}}: {{ $invoice->client->phone }}<br/>
            @endif
            @if($invoice->client->email != '')
                {{trans('application.email')}}: {{ $invoice->client->email }}.
            @endif
        </div>
    </div>
    <div style="clear: both"></div>
    <div class="col-md-12">
        <table class="table">
            <tr style="margin-bottom:30px;background: #2e3e4e;color: #fff;" class="item_table_header">
                <th style="width:50%">{{trans('application.product')}}</th>
                <th style="width:10%" class="text-center">{{trans('application.quantity')}}</th>
                <th style="width:15%" class="text-right">{{trans('application.price')}}</th>
                <th style="width:10%" class="text-center">{{trans('application.tax')}}</th>
                <th style="width:15%" class="text-right">{{trans('application.total')}}</th>
            </tr>
            <tbody id="items">
            @foreach($invoice->items->sortBy('item_order') as $item)
                <tr class="items">
                    <td><b>{!! $item->item_name !!}</b><br/>{!! htmlspecialchars_decode(nl2br(e($item->item_description)),ENT_QUOTES) !!}</td>
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
        <div class="col-md-6" style="padding: 7% 25% 0 10%;width: 30%; text-transform: uppercase">
            @if($invoiceSettings && $invoiceSettings->show_status)
                <div class="{{ $invoice->status == 2 ? 'invoice_status_paid' : 'invoice_status_cancelled' }}">
                    {{ statuses()[$invoice->status]['label']  }}
                </div>
            @endif
        </div>
        <table class="table">
            <tr>
                <th style="width:75%" class="text-right">{{trans('application.subtotal')}}</th>
                <td class="text-right">
                    <span id="subTotal">{{ format_amount($invoice->totals['subTotal']) }}</span>
                </td>
            </tr>
            <tr>
                <th class="text-right">{{trans('application.tax')}}</th>
                <td class="text-right">
                    <span id="taxTotal">{{ format_amount($invoice->totals['taxTotal']) }}</span>
                </td>
            </tr>
            @if($invoice->totals['discount'] > 0)
                <tr>
                    <th class="text-right">{{trans('application.discount')}}</th>
                    <td class="text-right">
                        <span id="taxTotal">{{ format_amount($invoice->totals['discount']) }}</span>
                    </td>
                </tr>
            @endif
            <tr>
                <th class="text-right">{{trans('application.total')}}</th>
                <td class="text-right">
                    <span id="grandTotal">{{ format_amount($invoice->totals['grandTotal']) }}</span>
                </td>
            </tr>
            <tr>
                <th class="text-right">{{trans('application.paid')}}</th>
                <td class="text-right">
                    <span id="grandTotal">{{ format_amount($invoice->totals['paidFormatted']) }}</span>
                </td>
            </tr>
            @if($invoice->totals['amountDue'] > 0)
            <tr class="amount_due">
                <th class="text-right">{{trans('application.amount_due')}}:</th>
                <td class="text-right">
                    <span id="grandTotal">{{ $invoice->currency.' '.format_amount($invoice->totals['amountDue']) }}</span>
                </td>
            </tr>
            @endif
        </table>
    </div>
    <div class="col-md-12">
        <h4><b>Amount in words</b><br>
            <span style="font-family: 'Arial Narrow', Arial, sans-serif; 12px">
                <?php
                $amount_in_word = '';
                //$amount_in_word = $currencyTransformer->toWords($invoice->totals['amountDue']*100, 'NGN');
                $actual_amt = $invoice->totals['grandTotal'];
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
        <small>
            @if($invoice->notes)
                <h4 class="invoice_title">{{trans('application.notes')}}</h4><hr class="separator"/>
                {!! htmlspecialchars_decode($invoice->notes, ENT_QUOTES) !!}
            @endif
            @if($invoice->terms)
                <h4 class="invoice_title">{{trans('application.terms')}}</h4><hr class="separator"/>
                {!! htmlspecialchars_decode($invoice->terms, ENT_QUOTES) !!}
            @endif
        </small>
    </div>
    @if($invoice->totals['amountDue'] > 0 && $invoiceSettings->show_pay_button)
        <div class="col-md-12" style="margin-top:20px;text-align: right">
          <a target="_blank" href="{{url('clientarea/cinvoices')}}" style="float:right" class="btn-success">@lang('application.pay_invoice')</a>
        </div>
    @endif
</div>
</body></html>
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
    .to_address{width: 330px;float: right;height:200px}
    .capitalize{text-transform: uppercase}
    .invoice_status_cancelled {font-size : 20px;text-align : center;color: #cc0000;border: 1px solid #cc0000;}
    .invoice_status_paid {font-size : 25px;text-align : center;color: #82b440;border: 1px solid #82b440;}
    .btn-success {background-color: #4CAF50;border: none;color: white;padding: 12px 22px;text-align: center;text-decoration: none;display: inline-block;font-size: 16px;}
</style>
