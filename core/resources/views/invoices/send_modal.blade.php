@extends('modal')
@section('content')
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title"><i class="fa fa-paper-plane"></i> {{trans('application.send_invoice')}}</h5>
            </div>
            {!! Form::open(['route' => ['email_invoice'], 'class' => 'ajax-submit']) !!}
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-10">
                        <div class="form-group">
                            {{Form::hidden('invoice_id',$invoice->uuid)}}
                            {!! Form::label('email', trans('application.email').'*') !!}
                            {!! Form::text('email', $invoice->client ? $invoice->client->email : null, ['class' => 'form-control input-sm', 'required']) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('subject', trans('application.subject').'*') !!}
                            {!! Form::text('subject', $template->subject ?? '', ['class' => 'form-control input-sm', 'required']) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('message', trans('application.message').'*') !!}
                            {!! Form::textarea('message', $template->body ?? '', ['class' => 'form-control', 'required']) !!}
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label><b>{{trans('application.invoice_tags')}}</b></label><br>
                        <p>{invoice_number} {invoice_amount} {invoice_logo} </p>
                        <label><b>{{trans('application.client_tags')}}</b></label>
                        <p>{client_name} {client_email} {client_number}</p>
                        <label><b>{{trans('application.company_tags')}}</b></label><br>
                        <p>{company_name} {company_email} {company_website} {contact_person}</p>
                        <label><b>{{trans('application.users_tags')}}</b></label><br>
                        {username} {password} {login_link}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                {!! form_buttons() !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    <script>$('#message').wysihtml5({image:false,link:false});</script>
@endsection