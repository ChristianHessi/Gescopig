<header class="main-header">

    <!-- Logo -->
    <a href="{{ url('/') }}" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>GSP</b></span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>GESCOPIG</b></span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- Messages: style can be found in dropdown.less-->
                @can('read enseignements')
                <li class="dropdown messages-menu">
                    <!-- Menu toggle button -->

                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        Enseignements <i class="fa fa-envelope-o"></i>
                        <span class="label label-success">{!! $enseignements_notif->count() !!}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header bg-success">Vous avez {!! $enseignements_notif->count() !!} nouvelles notifications</li>
                        <li>
                            <!-- inner menu: contains the messages -->
                            <ul class="menu">
                                @foreach($enseignements_notif as $enseignement)
                                        <li><!-- start message -->
                                            <a href="{!! route('enseignements.edit', [$enseignement->ingoing->id]) !!}">
                                                <h5>
                                                    {!! $enseignement->ingoing->specialite->slug .' '. $enseignement->ingoing->ecue->semestre->cycle->niveau !!} - {!! substr($enseignement->ingoing->ecue->title, 0, 25) !!}
                                                    <small class="pull-right"><i class="fa fa-clock-o"></i> {!! $enseignement->ingoing->updated_at->format('d/m/Y') !!}</small>
                                                </h5>
                                                <!-- The message -->
                                                @if(!$enseignement->ingoing->progression)
                                                    <p>fiche de Progression</p>
                                                @endif

                                                @if(!$enseignement->ingoing->communication)
                                                    <p>fiche de communication</p>
                                                @endif

                                                @if(!$enseignement->ingoing->cc)
                                                    <p>Contrôle continu </p>
                                                @endif
                                            </a>
                                        </li>
                                @endforeach
                            </ul>
                            <!-- /.menu -->
                        </li>
                        {{--<li class="footer"><a href="#">See All Messages</a></li>--}}
                    </ul>
                </li>
                @endcan
                <!-- /.messages-menu -->

                <!-- Notifications Menu -->
                @can('read absences')
                <li class="dropdown messages-menu">
                    <!-- Menu toggle button -->
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        Absences <i class="fa fa-bell-o"></i>
                        <span class="label label-warning">{!! $absences_notif->count() !!}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">Vous avez {!! $absences_notif->count() !!} notifications d'absences</li>
                        <li>
                            <!-- Inner Menu: contains the notifications -->

                            <ul class="menu">
                                @if($absences_notif->count())
                                @foreach($absences_notif as $absence)
                                <li><!-- start notification -->
                                    <a href="{!! route('absences.edit', [$absence->ingoing->id, $absence->ingoing->absences->last()->ecue->semestre_id]) !!}">
                                        <h5>
                                            {!! $absence->ingoing->apprenant->nom .' - '. $absence->ingoing->specialite->slug .''. $absence->ingoing->cycle->niveau !!}
                                            <small class="pull-right"><i class="fa fa-clock-o"></i> {!! $absence->ingoing->updated_at->format('d/m/Y') !!}</small>
                                        </h5>
                                        <p>{!! ' possède deja '. $absence->ingoing->absences->count(). ' absences' !!}</p>
                                    </a>
                                </li>
                                @endforeach
                                @endif
                                <!-- end notification -->
                            </ul>

                        </li>
                    </ul>
                </li>
                @endcan
                {{--<!-- Tasks Menu -->--}}
                {{--<li class="dropdown tasks-menu">--}}
                    {{--<!-- Menu Toggle Button -->--}}
                    {{--<a href="#" class="dropdown-toggle" data-toggle="dropdown">--}}
                        {{--<i class="fa fa-flag-o"></i>--}}
                        {{--<span class="label label-danger">9</span>--}}
                    {{--</a>--}}
                    {{--<ul class="dropdown-menu">--}}
                        {{--<li class="header">You have 9 tasks</li>--}}
                        {{--<li>--}}
                            {{--<!-- Inner menu: contains the tasks -->--}}
                            {{--<ul class="menu">--}}
                                {{--<li><!-- Task item -->--}}
                                    {{--<a href="#">--}}
                                        {{--<!-- Task title and progress text -->--}}
                                        {{--<h3>--}}
                                            {{--Design some buttons--}}
                                            {{--<small class="pull-right">20%</small>--}}
                                        {{--</h3>--}}
                                        {{--<!-- The progress bar -->--}}
                                        {{--<div class="progress xs">--}}
                                            {{--<!-- Change the css width attribute to simulate progress -->--}}
                                            {{--<div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar"--}}
                                                 {{--aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">--}}
                                                {{--<span class="sr-only">20% Complete</span>--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--</a>--}}
                                {{--</li>--}}
                                {{--<!-- end task item -->--}}
                            {{--</ul>--}}
                        {{--</li>--}}
                        {{--<li class="footer">--}}
                            {{--<a href="#">View all tasks</a>--}}
                        {{--</li>--}}
                    {{--</ul>--}}
                {{--</li>--}}
                <!-- User Account Menu -->
                {{--<li class="dropdown user user-menu">--}}
                    {{--<!-- Menu Toggle Button -->--}}
                    {{--<a href="#" class="dropdown-toggle" data-toggle="dropdown">--}}
                        {{--<!-- The user image in the navbar-->--}}
                        {{--<img src="http://localhost/pigier/public/adminlte/img/user2-160x160.jpg" class="user-image" alt="User Image">--}}
                        {{--<!-- hidden-xs hides the username on small devices so only the image appears. -->--}}
                        {{--<span class="hidden-xs">Alexander Pierce</span>--}}
                    {{--</a>--}}
                    {{--<ul class="dropdown-menu">--}}
                        {{--<!-- The user image in the menu -->--}}
                        {{--<li class="user-header">--}}
                            {{--<img src="http://localhost/pigier/public/adminlte/img/user2-160x160.jpg" class="img-circle" alt="User Image">--}}


                            {{--<p>--}}
                                {{--Alexander Pierce - Web Developer--}}
                                {{--<small>Member since Nov. 2012</small>--}}
                            {{--</p>--}}
                        {{--</li>--}}

                        {{--<div class="pull-left image">--}}
                            {{--<img src="http://localhost/pigier/public/adminlte/img/user2-160x160.jpg" class="img-circle" alt="User Image">--}}
                        {{--</div>--}}
                        {{--<!-- Menu Body -->--}}
                        {{--<li class="user-body">--}}
                            {{--<div class="row">--}}
                                {{--<div class="col-xs-4 text-center">--}}
                                    {{--<a href="#">Followers</a>--}}
                                {{--</div>--}}
                                {{--<div class="col-xs-4 text-center">--}}
                                    {{--<a href="#">Sales</a>--}}
                                {{--</div>--}}
                                {{--<div class="col-xs-4 text-center">--}}
                                    {{--<a href="#">Friends</a>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                            {{--<!-- /.row -->--}}
                        {{--</li>--}}
                        {{--<!-- Menu Footer-->--}}
                        {{--<li class="user-footer">--}}
                            {{--<div class="pull-left">--}}
                                {{--<a href="#" class="btn btn-default btn-flat">Profile</a>--}}
                            {{--</div>--}}
                            {{--<div class="pull-right">--}}
                                {{--<a href="#" class="btn btn-default btn-flat" id="logout">Sign out</a>--}}
                                {{--<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hide">--}}
                                    {{--{{ csrf_field() }}--}}
                                {{--</form>--}}
                            {{--</div>--}}
                        {{--</li>--}}
                    {{--</ul>--}}
                {{--</li>--}}

                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        {{ Auth::user()->name }} <span class="caret"></span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                                         document.getElementById('logout-form').submit();">
                                {{ __('Deconnexion') }}
                            </a>
                        </li>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button -->
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>

            </ul>
        </div>
    </nav>
</header>