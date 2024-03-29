@extends('app')
@section('content')
<div class="col-md-12 content-header" >
    <h1><i class="fa fa-money"></i> {{trans('application.payments')}}</h1>
</div>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                @if(hasPermission('add_payment'))
                    <div class="box-header with-border">
                        <h3 class="box-title pull-right">
                            <div class="box-tools ">
                                <a href="{{ route('payments.create') }}" class="btn btn-primary btn-xs pull-right" data-toggle="ajax-modal"> <i class="fa fa-plus"></i> {{trans('application.record_payment')}}</a>
                            </div>
                        </h3>
                    </div>
                @endif
                <div class="box-body">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover ajax_datatable">
                        <thead>
                        <tr>
                            <th>{{trans('application.invoice_number')}}</th>
                            <th>{{trans('application.client')}}</th>
                            <th>{{trans('application.date')}}</th>
                            <th>{{trans('application.payment_method')}}</th>
                            <th>Staff</th>
                            <th class="text-right">{{trans('application.amount')}}</th>
                            <th>{{trans('application.action')}} </th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.ajax_datatable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: '{{route('payments.index')}}',
                order: [],
                columnDefs: [{
                    "orderable": false,
                    "targets": 0
                }],
                oLanguage: {
                    "sProcessing":"{{trans('application.processing')}}"
                },
                columns:[
                    {data: 'number',name:'invoice.number',orderable:false},
                    {data: 'client', 'searchable':false, name: 'client.name',orderable:false},
                    {data: 'payment_date',orderable:false},
                    {data: 'payment_method',name:'payment_method.name',orderable:false},
                    {data: 'user_id','searchable':false,orderable:false},
                    {data: 'amount', 'searchable':false,className: "text-right",orderable:false},
                    {data: 'action', 'searchable':false,orderable:false,className: "text-right"}
                ]
            });
            $('div.dataTables_filter input').addClass('form-control input-sm');
            $('div.dataTables_length select').addClass('form-control input-sm');
        });
    </script>
@endsection

