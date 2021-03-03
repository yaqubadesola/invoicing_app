<?php 
use NumberToWords\NumberToWords; 
// create the number to words "manager" class
$numberToWords = new NumberToWords();

// build a new currency transformer using the RFC 3066 language identifier
$currencyTransformer = $numberToWords->getCurrencyTransformer('en');
?>
@extends('app')
@section('content')
<div class="col-md-12 content-header" >
    <h1><i class="fa fa-quote-left"></i> {{trans('application.invoices')}}</h1>
</div>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12" style="max-width:1040px;">
                            @if (session()->has('flash_notification'))
                                {!! message() !!}
                            @endif
                             <a href="{{ route('invoices.index') }}" class="btn btn-info btn-sm"> <i class="fa fa-chevron-left"></i> {{trans('application.back')}}</a>
                            @if(hasPermission('send_invoice'))
                                <a href="{{route('invoice_send_modal',$invoice->uuid)}}" data-toggle="ajax-modal" class="btn btn-success btn-sm pull-right" style="margin-left: 5px"> <i class="fa fa-send"></i> {{trans('application.send')}}</a>
                            @endif
                                <a href="{{ url('invoices/pdf', $invoice->uuid) }}" class="btn btn-primary btn-sm pull-right" style="margin-left: 5px"> <i class="fa fa-download"></i> {{trans('application.download')}}</a>
                            @if(hasPermission('edit_invoice'))
                                @if( $invoice->status != 2)
                                <a href="{{ route('invoices.edit', $invoice->uuid) }}" class="btn btn-warning btn-sm pull-right"><i class="fa fa-pencil"></i> {{trans('application.edit')}}</a>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="invoice">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="panel-body">
                                        @if($invoiceSettings && $invoiceSettings->logo != '')
                                        <img src="{{image_url($invoiceSettings->logo)}}" alt="logo" width="100%"/>
                                        @endif
                                     </div>
                                     <div style="font-size:10px; text-align:center"><b>Generators || Transformer || Inverters
                                        || UPS || ELV Panels <br>   Distribution Boards  &amp; Installation Accessories</b></div>
                                </div>
                                <div class="col-md-8 text-right">
                                    <div class="panel-body" style="font-size: 16px;font-weight: bold;padding:0">
                                        <div class="col-xs-12 text-right"> <h1 style="margin:0">{{trans('application.invoice')}}</h1></div>
                                        <div class="col-xs-9 text-right invoice_title">{{trans('application.reference')}}</div>
                                        <div class="col-xs-3 text-right">{{ ucwords(substr($settings->name,0,3))}}/{{$invoice->number }}</div>
                                        <div class="col-xs-9 text-right invoice_title">{{trans('application.date')}}</div>
                                        <div class="col-xs-3 text-right">{{ format_date($invoice->invoice_date) }}</div>
                                        @if($invoice->due_date != '0000-00-00')
                                        <div class="col-xs-9 text-right invoice_title">{{trans('application.due_date')}}</div>
                                        <div class="col-xs-3 text-right">{{ format_date($invoice->due_date) }}</div>
                                        @endif
                                        @if($settings && $settings->gst != '')
                                        <div class="col-xs-9 text-right invoice_title">{{trans('application.gst_number')}}</div>
                                        <div class="col-xs-3 text-right">{{ $settings ? $settings->gst : '' }}</div>
                                        @endif
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
                                            <h4>{{ $invoice->client->name }}</h4>
                                            {{ $invoice->client->address1 ? $invoice->client->address1.',' : '' }} {{ $invoice->client->state ? $invoice->client->state : '' }}<br/>
                                            {{ $invoice->client->city ? $invoice->client->city.',' : '' }} {{ $invoice->client->postal_code ? $invoice->client->postal_code.','  : ''  }}<br/>
                                            {{ $invoice->client->country }}<br/>
                                            @if($invoice->client->phone != '')
                                                {{trans('application.phone')}}: {{ $invoice->client->phone }}<br/>
                                            @endif
                                            @if($invoice->client->email != '')
                                                {{trans('application.email')}}: {{ $invoice->client->email }}.
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
                                                {{$invoice->invoice_title}}
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
                                @foreach($invoice->items->sortBy('item_order') as $item)
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
                            </div>
                            <div class="col-md-6" style="padding: 7% 12% 0 15%; text-transform: uppercase">
                                @if($invoiceSettings && $invoiceSettings->show_status)
                                    <div class="{{ $invoice->status == 2 ? 'invoice_status_paid' : 'invoice_status_cancelled' }}">
                                        {{ statuses()[$invoice->status]['label']  }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table"><tbody>
                                        <tr>
                                            <th style="width:50%">{{trans('application.subtotal')}}</th>
                                            <td class="text-right">
                                                <span id="subTotal">{{ format_amount($invoice->totals['subTotal']) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{trans('application.tax')}}</th>
                                            <td class="text-right">
                                                <span id="taxTotal">{{ format_amount($invoice->totals['taxTotal']) }}</span>
                                            </td>
                                        </tr>
                                        @if($invoice->totals['discount'] > 0)
                                        <tr>
                                            <th>{{trans('application.discount')}}</th>
                                            <td class="text-right">
                                                <span id="taxTotal">{{ format_amount($invoice->totals['discount']) }}</span>
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>{{trans('application.total')}}</th>
                                            <td class="text-right">
                                                <span id="grandTotal">{{ format_amount($invoice->totals['grandTotal']) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{trans('application.paid')}}</th>
                                            <td class="text-right">
                                                <span id="grandTotal">{{ format_amount($invoice->totals['paidFormatted']) }}</span>
                                            </td>
                                        </tr>
                                        @if($invoice->totals['amountDue'] >= 0)
                                        <tr class="amount_due">
                                            <th>{{trans('application.amount_due')}}:</th>
                                            <td class="text-right">
                                                <span class="currencySymbol" style="display: inline-block;">{{ $invoice->currency }} </span>
                                                <span id="amountDue">{{ $invoice->totals['amountDue'] }}</span>
                                            </td>
                                        </tr>
                                        @endif
                                       
                                        </tbody></table>
                                </div>
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
                                            $amount_in_word = $converted_amt . " naira ";
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cleafix"></div>
    </div>
</section>
@endsection


