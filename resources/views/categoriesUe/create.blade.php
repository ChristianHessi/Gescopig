@extends('layouts.app')

@section('content')

    <div class="content col-md-10">
        <h1>{{ __('Creation de Categorie UE') }}</h1>

        <div class="clearfix"></div>
        @include('flash::message')
        <div class="clearfix"></div>

        <div class="box box-primary">
            {{ Form::open(['route' => 'catUes.store']) }}
            <div class="box-body">

                <div class="form-group col-sm-8">
                    {!! Form::label('title', 'Titre :') !!}
                    {!! Form::text('title', null, ['class' => 'form-control']) !!}
                </div>

                <div class="form-group col-sm-12">
                    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                    <a href="{!! route('catUes.index') !!}" class="btn btn-default">Cancel</a>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('css')

    <link rel="stylesheet" href="{{ url('css/easy-autocomplete.min.css') }}">

@endsection

@section('scripts')
    <script src="{{ url('js/jquery.easy-autocomplete.min.js') }}"></script>
    <script type="text/javascript">
        $(function(){
            var title = {
                data : {!! $catUes !!},
                getValue: 'title',
                list: {
                    match:{
                        enabled: true
                    },
                    onClickEvent: function(e){

                        var id = $('#ecue').getSelectedItemData().id;
                        window.location.href = 'http://'+ window.location.host +'/ecues/' + id +'/edit';
                    }
                }
            };
            $('#ecue').easyAutocomplete(title);
        });
    </script>

@endsection