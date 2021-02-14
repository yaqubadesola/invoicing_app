<aside class="main-sidebar">    <!-- sidebar: style can be found in sidebar.less -->    <section class="sidebar">        <!-- Sidebar user panel -->        <div class="user-panel">            @if(auth()->guard('user')->check())            <div class="pull-left image">                <img src="{{Auth::guard('user')->user()->photo != '' ? image_url('uploads/client_images/'.Auth::guard('user')->user()->photo) : image_url('uploads/defaultavatar.png')}}" class="img-circle" alt="User Image" />            </div>            <div class="pull-left info">                <p> {{  auth()->guard('user')->user()->name }} </p>                <a href="#"><i class="fa fa-circle text-success"></i> {{trans('application.online')}}</a>            </div>            @endif        </div>        <!-- sidebar menu: : style can be found in sidebar.less -->        <ul class="sidebar-menu">            <li class="header">{{trans('application.main_menu')}}</li>            <li class="{{ Form::menu_active('clientarea/home') }}"><a href="{{ route('client_dashboard') }}"><i class="fa fa-home"></i><span>{{trans('application.dashboard')}}</span></a></li>            <li class="{{ Form::menu_active('clientarea/cinvoices') }}"><a href="{{ route('cinvoices.index') }}"><i class="fa fa-file-pdf-o"></i><span>{{trans('application.invoices')}}</span></a></li>            <li class="{{ Form::menu_active('clientarea/cestimates') }}"><a href="{{ route('cestimates.index') }}"><i class="fa fa-list-alt"></i><span>{{trans('application.estimates')}}</span> </a></li>            <li class="{{ Form::menu_active('clientarea/cpayments') }}"><a href="{{ route('cpayments.index') }}"><i class="fa fa-money"></i><span>{{trans('application.payments')}}</span></a></li>            <li class="{{ Form::menu_active('clientarea/reports') }}"><a href="{{ url('clientarea/reports') }}"><i class="fa fa-line-chart"></i><span>{{trans('application.reports')}}</span></a></li>            <li class="header">{{trans('application.account_menu')}}</li>            <li class="{{ Form::menu_active('clientarea/cprofile') }}"><a href="{{ url('clientarea/cprofile') }}"><i class="fa fa-user-md "></i><span>{{trans('application.profile')}}</span></a></li>            <li class="{{ Form::menu_active('clientarea/logout') }}"><a href="{{ route('client_logout') }}"><i class="fa fa-power-off"></i> <span>{{trans('application.logout')}}</span></a></li>        </ul>    </section>    <!-- /.sidebar --></aside>