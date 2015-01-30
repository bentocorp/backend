

@extends('admin.master')

@section('content')


<!--
******************************************************************************
CRUD Dish
******************************************************************************
-->
<h1>{{{$mode}}} {{{$dish->name}}}</h1>

<!-- Show the form -->
{{ Form::open(array('url' => "admin/dish/edit/$dish->pk_Dish")) }}
{{ Form::model($dish) }}

    {{ Form::label('name', 'Dish Name') }} <br>
    {{ Form::text('name') }}

{{ Form::close() }}



  
@stop