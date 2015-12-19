

@extends('admin.master')

@section('content')


<!--
******************************************************************************
CRUD Driver
******************************************************************************
-->
<h1>{{{$title}}}</h1>
<br>

<!-- Show the form -->
{{ Form::open(array('class' => 'form-horizontal')) }}

    <!-- Bind to model -->
    
    <?php if (isset($record)) echo Form::model($record) ?>

    <div class="form-group">
        {{ Form::label('firstname', 'First Name *', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('firstname', null, array('class' => 'form-control')) }}</div>
    </div>
    
    <div class="form-group">
        {{ Form::label('lasttname', 'Last Name *', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('lastname', null, array('class' => 'form-control')) }}</div>
    </div>
        
    <div class="form-group">
        {{ Form::label('email', 'Email *', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('email', null, array('class' => 'form-control')) }}</div>
    </div>
    
    <div class="form-group">
        {{ Form::label('mobile_phone', 'Mobile Phone *', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('mobile_phone', null, array('class' => 'form-control')) }}</div>
    </div>
    
    <div class="form-group">
        {{ Form::label('notes', 'Notes', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::textarea('notes', null, array('class' => 'form-control')) }}</div>
    </div>
    
    
    <div class="form-group">
      <div class="col-sm-7"><button type="submit" class="btn btn-success pull-right">Save</button></div>
    </div>

{{ Form::close() }}



  
@stop