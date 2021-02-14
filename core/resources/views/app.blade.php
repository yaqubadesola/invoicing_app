<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{get_company_name()}}</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{image_url('favicon.png') }}">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.styles')
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body class="skin-blue sidebar-mini">
<div class="wrapper animsition">
<header class="main-header">
    <a href="{{url('/')}}" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>G</b>P E</span>
        <!-- logo for regular state and mobile devices -->
        <?php
            $settings = \App\Models\Setting::all();
        ?>
        @if(!empty($settings))
            {!! Html::image(image_url($settings[0]->logo), 'logo', array('class' => 'medium','width'=>'100%')) !!}
        @else
        <span class="medium"><img src="{{image_url('dummy_logo.png')}}" alt="logo" style="width: 100%"/></span>
        @endif
    </a>
<!-- Header Navbar: style can be found in header.less -->
<nav class="navbar navbar-fixed-top navbar-default" role="navigation">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
    </a>
<!-- Navbar Right Menu -->
<div class="navbar-custom-menu">
<ul class="nav navbar-nav">
    <li class="dropdown">
        <?php
            if(Session::has('applocale')){
                $current_lang = get_current_language(Session::get('applocale'));
                if(!$current_lang){
                    $current_lang = get_default_language();
                    if(!$current_lang){
                        $current_lang = get_current_language(App::getLocale());
                    }
                }
            }
            else{
                $current_lang = get_default_language();
                if(!$current_lang){
                    $current_lang = get_current_language(App::getLocale());
                }
            }
            $current_flag = $current_lang->flag != '' ? $current_lang->flag : 'placeholder_Flag.jpg';
        ?>
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <img src="{{image_url('flags/'.$current_flag) }}" class="language-img">{{ $current_lang->locale_name }} <b class="caret"></b>
        </a>
        <ul class="dropdown-menu">
            <?php $languages = get_languages(); ?>
            @foreach($languages as $language)
                @if ($language->short_name != $current_lang->short_name)
                    <?php $flag = $language->flag != '' ? $language->flag : 'placeholder_Flag.jpg'; ?>
                <li>
                    <a rel="alternate" href="{{ route('admin_lang_switch', $language->short_name) }}">
                        <img src="{{image_url('flags/'.$flag) }}" class="language-img">{{ $language->locale_name }}
                    </a>
                </li>
                    <li class="divider"></li>
                @endif
            @endforeach
        </ul>
    </li>
<!-- User Account: style can be found in dropdown.less -->
<li class="dropdown user user-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        @if(auth()->guard('admin')->check())
        <img src="{{ Auth::guard('admin')->user()->photo != '' ? image_url('uploads/'.Auth::guard('admin')->user()->photo) : image_url('uploads/defaultavatar.png') }}" class="user-image" alt="User Image"/>
        <span class="hidden-xs"> {{  auth()->guard('admin')->user()->name }} </span>
        @endif
        <b class="caret"></b>
    </a>
    <ul class="dropdown-menu">
        <!-- User image -->
        <li class="user-header">
            @if(auth()->guard('admin')->check())
            <img src="{{Auth::guard('admin')->user()->photo != '' ? image_url('uploads/'.Auth::guard('admin')->user()->photo) : image_url('uploads/defaultavatar.png') }}" class="img-circle" alt="User Image" />
            <p>{{  auth()->guard('admin')->user()->name }} </p>
            @endif
        </li>
        <!-- Menu Footer-->
        <li class="user-footer">
            <div class="pull-left">
                <a href="{{ url('profile') }}" class="btn btn-primary btn-sm btn-flat">{{trans('application.edit_profile')}}</a>
            </div>
            <div class="pull-right">
                <a href="{{ route('admin_logout') }}" class="btn btn-danger btn-sm btn-flat">{{trans('application.logout')}}</a>
            </div>
        </li>
    </ul>
</li>
</ul>
</div>
</nav>
</header>
<!-- Left side column. contains the logo and sidebar -->
@include('nav')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
@yield('content')
</div><!-- /.content-wrapper -->
    <div id="ajax-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static"></div>
    @if(!is_verified())
    <div id="activation-modal" class="modal fade" role="dialog" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Verification of the license</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open(['url'=>'/settings/verify','id'=>'verify_form']) !!}
                    <div class=" col-xs-3 col-sm-3">
                        <img src="{{config('app.images_path').'lock.png'}}" width="100%">
                    </div>
                    <div class="col-xs-9 col-sm-9 ">
                        <div class="form-group">
                            <label for="envato_username">Envato Username</label>
                            <input type="text" class="form-control input-sm" required name="envato_username" id="envato_username" placeholder="Enter your envato username here"/>
                        </div>
                        <div class="form-group">
                            <label for="envato_username">Purchase Code</label>
                            <input type="text" class="form-control input-sm" name="purchase_code" id="purchase_code" placeholder="Enter your purchase code here"/>
                            <span style="font-size:12px;"><a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank">Where can I find my purchase code ?</a></span>
                        </div>
                        <div class="form-group">
                            <a href="javascript:" onclick="checkLicense()" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-check"></span>Verify</a>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12">
                        <div class="alert alert-info" style="font-size:12px;  margin-bottom: 0px;" >
                            <span class="glyphicon glyphicon-warning-sign" style="margin-right: 12px;float: left;font-size: 22px;margin-top: 10px;margin-bottom: 10px;"></span>
                            Each website using this plugin needs a legal license (1 license = 1 website).<br/>
                            To read find more information on envato licenses,
                            <a href="https://codecanyon.net/licenses/standard" target="_blank">click here</a>.<br/>
                            If you need to buy a new license of this plugin, <a href="https://codecanyon.net/item/classic-invoicer/6193251?ref=elantsys" target="_blank">click here</a>.
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    {!! Form::close() !!}
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    @endif
</div>
@include('partials.scripts')
@yield('scripts')
@include('common.common_js')
@if (session()->has('flash_notification'))
    <?php
        $notification = session()->pull('flash_notification')[0];
        $message_type = $notification->level;
    ?>
    @if($message_type == 'success')
        <script>
            $.amaran({
                'theme'     :'awesome ok',
                'content'   :{
                    title:'Success !',
                    message:'{{$notification->message}}!',
                    info:'',
                    icon:'fa fa-check-square-o'
                },
                'position'  :'bottom right',
                'outEffect' :'slideBottom'
            });
        </script>
    @elseif($message_type == 'danger')
        <script>
            $.amaran({
                'theme'     :'awesome error',
                'content'   :{
                    title:'Error !',
                    message:'{{$notification->message}}!',
                    info:'',
                    icon:'fa fa-times-circle-o'
                },
                'position'  :'bottom right',
                'outEffect' :'slideBottom'
            });
        </script>
    @endif
@endif
</body>
</html>