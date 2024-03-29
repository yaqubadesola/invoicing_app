@extends('app')
@section('content')
    <div class="col-md-12 content-header" >
        <h1><i class="fa fa-quote-left"></i> {{ trans('application.estimates') }}</h1>
    </div>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="row">
                        <div class="col-md-6" style="width:880px;margin-left:20px"><br/>
                            <a href="{{ route('estimates.index') }}" class="btn btn-info btn-sm"> <i class="fa fa-chevron-left"></i> {{ trans('application.back') }}</a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="invoice">
                                {!! Form::open(['route' => ['estimates.store'], 'id' => 'estimate_form', 'data-toggle'=>"validator", 'role' =>"form"]) !!}
                                <div class="col-md-12">
                                    <div class="text-right">
                                        <h1 style="margin:0">{{ trans('application.estimate') }}</h1>
                                    </div>
                                    <div class="col-md-7" style="padding: 0px">
                                        <div class="contact to">
                                            <div class="form-group">
                                                {!! Form::label('client', trans('application.client')) !!}
                                                <div class="input-group col-md-9">
                                                    {!! Form::select('client',$clients,null, ['class' => 'form-control chosen input-sm', 'id' => 'client', 'required']) !!}
                                                    <span class="input-group-addon">
                                                        <a href="{{ route('clients.create') }}" data-toggle="ajax-modal" class="ajaxNonReload" data-element="client"><i class="fa fa-plus"></i></a>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('currency', trans('application.currency')) !!}
                                                <div class="input-group col-md-9">
                                                    {!! Form::select('currency',$currencies,$default_currency->code ?? null, ['class' => 'form-control input-sm chosen', 'required']) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5" style="padding: 0px">
                                        <div class="form-group">
                                            {!! Form::label('estimate_no', trans('application.estimate_number')) !!}
                                            {!! Form::text('estimate_no',$estimate_num, ['class' => 'form-control input-sm', 'id' => 'estimate_no', 'required']) !!}
                                        </div>
                                        <div class="form-group">
                                            {!! Form::label('estimate_date', trans('application.estimate_date')) !!}
                                            {!! Form::text('estimate_date',date('Y-m-d'), ['class' => 'form-control datepicker input-sm' , 'id' => 'estimate_date', 'required','readonly']) !!}
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
                                        {!! Form::text('estimate_title','', ['class' => 'form-control input-sm' , 'id' => 'estimate_title']) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                </div>
                                <div class="col-md-12">
                                    <table class="table table-striped" id="item_table">
                                        <thead class="item-table-header">
                                        <tr>
                                            <th width="5%"></th>
                                            <th width="20%">{{ trans('application.product') }}</th>
                                            <th width="35%">{{ trans('application.description') }}</th>
                                            <th width="10%" class="text-center">{{trans('application.quantity')}}</th>
                                            <th width="10%" class="text-center">{{trans('application.price')}}</th>
                                            <th width="10%" class="text-center">{{trans('application.tax')}}</th>
                                            <th width="10%"class="text-right">{{trans('application.amount')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr class="item">
                                            <td></td>
                                            <td><div class="form-group">{!! Form::text('item_name',null, ['class' => 'form-control input-sm item_name', 'id'=>"item_name" , 'required']) !!}</div></td>
                                            <td><div class="form-group">{!! Form::textarea('item_description',null, ['class' => 'form-control item_description input-sm', 'id'=>"item_description", 'rows'=>'1' ]) !!}</div></td>
                                            <td><div class="form-group">{!! Form::input('number','quantity',null, ['class' => 'form-control calcEvent quantity input-sm', 'id'=>"quantity" , 'required', 'step' => 'any', 'min' => '0']) !!}</div></td>
                                            <td><div class="form-group">{!! Form::input('number','price',null, ['class' => 'form-control calcEvent price input-sm', 'id'=>"price", 'required','step' => 'any', 'min' => '0']) !!}</div></td>
                                            <td><div class="form-group">{!! Form::customSelect('tax',$taxes['options'],$taxes['default'], ['class' => 'form-control calcEvent tax input-sm', 'id'=>"tax"]) !!}</div></td>
                                            <td class="text-right"><span class="itemTotal">0.00</span></td>
                                        </tr>
                                        </tbody>
                                    </table></div>
                                <div class="col-md-6">
                                    <span id="btn_add_row" class="btn btn-sm btn-info "><i class="fa fa-plus"></i> {{trans('application.add_row')}}</span>
                                    <span id="btn_product_list_modal" class="btn btn-sm btn-primary "><i class="fa fa-plus"></i> {{ trans('application.add_from_products') }}</span>
                                </div>
                                <div class="col-md-6">
                                    <table class="table">
                                        <tbody>
                                        <tr>
                                            <th style="width:50%">{{trans('application.subtotal')}}</th>
                                            <td class="text-right">
                                                <span id="subTotal">0.00</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{trans('application.tax')}}</th>
                                            <td class="text-right">
                                                <span id="taxTotal">0.00</span>
                                            </td>
                                        </tr>
                                        <tr class="amount_due">
                                            <th>{{trans('application.total')}}:</th>
                                            <td class="text-right">
                                                <span class="currencySymbol"></span>
                                                <span id="grandTotal">0.00</span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('notes', trans('application.notes')) !!}
                                        {!! Form::textarea('notes',null, ['class' => 'form-control input-sm text_editor','rows' =>  '2']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('terms', trans('application.terms')) !!}
                                        {!! Form::textarea('terms',$settings ? $settings->terms : '', ['class' => 'form-control input-sm text_editor', 'rows' =>  '2']) !!}
                                    </div></div><div class="col-md-12">
                                    <button type="submit" class="btn btn-sm btn-success pull-right" id="saveEstimate"><i class="fa fa-save"></i> {{trans('application.save')}}</button></div>
                                {!!  Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('scripts')
    @include('estimates.partials._estimatesjs')
@endsection