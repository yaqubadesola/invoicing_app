@extends('app')
@section('content')
<div class="col-md-12 content-header" >
    <h1><i class="fa fa-quote-left"></i> {{trans('application.estimates')}}</h1>
</div>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box  box-primary">
                @if(hasPermission('add_estimate'))
                    <div class="box-header with-border">
                        <h3 class="box-title pull-right">
                            <div class="box-tools">
                                <a href="{{ route('estimates.create') }}" class="btn btn-primary btn-xs pull-right"> <i class="fa fa-plus"></i> {{trans('application.new_estimate')}}</a>
                            </div>
                        </h3>
                    </div>
                @endif
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover ajax_datatable">
                            <thead>
                            <tr>
                                <th>{{trans('application.estimate_number')}}</th>
                                <th>{{trans('application.client')}}</th>
                                <th>{{trans('application.date')}}</th>
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
                ajax: '{{route('estimates.index')}}',
                order: [],
                columnDefs: [{
                    "orderable": false,
                    "targets": 0
                }],
                oLanguage: {
                    "sProcessing":"{{trans('application.processing')}}"
                },
                columns:[
                    {data: 'estimate_no',name:'estimates.estimate_no',orderable:false},
                    {data: 'name', name: 'client.name',orderable:false},
                    {data: 'estimate_date',orderable:false},
                    {data: 'amount', 'searchable':false,className: "text-right",orderable:false},
                    {data: 'action', 'searchable':false,orderable:false,className: "text-right"}
                ]
            });
            $('div.dataTables_filter input').addClass('form-control input-sm');
            $('div.dataTables_length select').addClass('form-control input-sm');
        });
    </script>
@endsection

