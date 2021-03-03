@extends('app')
@section('content')
<div class="col-md-12 content-header" >
    <h1><i class="fa fa-quote-left"></i> {{trans('application.estimate')}}</h1>
</div>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12" style="max-width:1040px;">
                            <a href="{{ route('estimates.index') }}" class="btn btn-info btn-xs"> <i class="fa fa-chevron-left"></i> {{trans('application.back')}}</a>
                            <a href="{{ route('estimates.show', $estimate->uuid) }}" class="btn btn-primary btn-xs pull-right"> <i class="fa fa-search"></i> {{trans('application.preview')}}</a>
                            <span style="margin-right:5px" id="btn_convert_to_invoice" data-id="{{$estimate->uuid}}" class="btn btn-success btn-xs pull-right"> <i class="fa fa-mail-forward"></i> {{trans('application.make_invoice')}}</span>
                        </div>
                    </div>
                    <div class="row">
                    <div class="invoice">
                        {!! Form::model($estimate, ['route' => ['estimates.update', $estimate->uuid],  'method' => 'PATCH', 'id' => 'estimate_form', 'data-toggle'=>"validator", 'role' =>"form"]) !!}
                        <div class="col-md-12">
                            <div class="text-right"><h1 style="margin:0">{{trans('application.estimate')}}</h1></div>
                            <div class="col-md-7" style="padding: 0px">
                                <div class="contact to">
                                    <div class="form-group">
                                        {!! Form::label('client', trans('application.client')) !!}
                                        <div class="input-group col-md-9">
                                            {!! Form::select('client',$clients,$estimate->client_id, ['class' => 'form-control chosen input-sm', 'id' => 'client', 'required']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('currency', trans('application.currency')) !!}
                                        <div class="input-group col-md-9">
                                            {!! Form::select('currency',$currencies,null, ['class' => 'form-control chosen input-sm', 'required']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5" style="padding: 0px">
                                <div class="form-group">
                                    {!! Form::label('estimate_no', trans('application.estimate_number')) !!}
                                    {!! Form::text('estimate_no',null, ['class' => 'form-control input-sm', 'id' => 'estimate_no', 'required']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('estimate_date', trans('application.date')) !!}
                                    {!! Form::text('estimate_date',null, ['class' => 'form-control datepicker input-sm' , 'id' => 'estimate_date', 'required','readonly']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3" >
                            <div class="form-group text-right">
                            {!! Form::label('estimate_title', trans('application.estimate_title')) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::text('estimate_title',$estimate->estimate_title, ['class' => 'form-control input-sm' , 'id' => 'estimate_title']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                        </div>
                        <div class="col-md-12">
                            <table class="table table-striped" id="item_table">
                                <thead style="background: #2e3e4e;color: #fff;">
                                <tr>
                                    <th></th>
                                    <th width="20%">{{ trans('application.product') }}</th>
                                    <th width="35%">{{ trans('application.description') }}</th>
                                    <th width="10%">{{ trans('application.quantity') }}</th>
                                    <th width="15%">{{trans('application.price')}}</th>
                                    <th width="15%">{{trans('application.tax')}}</th>
                                    <th width="15%"class="text-right">{{trans('application.amount')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($estimate->items->sortBy('item_order') as $item)
                                <tr class="item">
                                    <td>
                                        <span class="btn btn-danger btn-xs deleteItem" data-id="{{ $item->uuid }}"><i class="fa fa-trash"></i></span>
                                        {!! Form::hidden('itemId',$item->uuid, ['id'=>'itemId', 'required']) !!}
                                    </td>
                                    <td>
                                        <div class="form-group">{!! Form::text('item_name',$item->item_name,['class' => 'form-control input-sm item_name', 'id'=>"item_name", 'required' ]) !!}</div>
                                    </td>
                                    <td>
                                        <div class="form-group">{!! Form::text('item_description',$item->item_description,['class' => 'form-control input-sm item_description', 'id'=>"item_description"]) !!}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-group">
                                            {!! Form::input('number', 'quantity',$item->quantity, ['class' => 'form-control calcEvent input-sm quantity', 'id'=>"quantity" , 'required', 'step' => 'any', 'min' => '0']) !!}
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="form-group">{!! Form::input('number','price',$item->price, ['class' => 'form-control calcEvent price input-sm', 'id'=>"price", 'required', 'step' => 'any', 'min' => '0']) !!}</div>
                                    </td>
                                    <td>
                                        <div class="form-group">{!! Form::customSelect('tax',$taxes['options'],$item->tax_id, ['class' => 'form-control calcEvent tax input-sm', 'id'=>"tax"]) !!}</div>
                                     </td>
                                    <td class="text-right"><span class="itemTotal">{{ $item->itemTotal }}</span></td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <span id="btn_add_row" class="btn btn-xs btn-info "><i class="fa fa-plus"></i> {{trans('application.add_row')}}</span>
                            <span id="btn_product_list_modal" class="btn btn-xs btn-primary "><i class="fa fa-plus"></i> {{ trans('application.add_from_products') }}</span>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tbody>
                                <tr>
                                    <th style="width:50%">{{trans('application.subtotal')}}</th>
                                    <td class="text-right">
                                        <span id="subTotal">{{ $estimate->totals['subTotal'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tax</th>
                                    <td class="text-right">
                                        <span id="taxTotal">{{ $estimate->totals['taxTotal'] }}</span>
                                    </td>
                                </tr>
                                <tr class="amount_due">
                                    <th>Total:</th>
                                    <td class="text-right">
                                        <span class="currencySymbol" style="display: inline-block;">{{ $estimate->currency }}</span>
                                        <span id="grandTotal">{{ $estimate->totals['grandTotal'] }}</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('notes', trans('application.notes')) !!}
                                {!! Form::textarea('notes',$estimate->notes, ['class' => 'form-control input-sm text_editor','rows' =>  '2']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('terms', trans('application.terms')) !!}
                                {!! Form::textarea('terms',$estimate->terms, ['class' => 'form-control input-sm text_editor', 'rows' =>  '2']) !!}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-xs btn-success pull-right" id="saveEstimate"><i class="fa fa-save"></i> {{trans('application.save')}}</button>
                        </div>
                        {!!  Form::close() !!}
                    </div>
                    </div>
                </div>
            </div>
        </div>>
    </div>
</section>
@endsection
@section('scripts')
    @include('estimates.partials._estimatesjs')
@endsection