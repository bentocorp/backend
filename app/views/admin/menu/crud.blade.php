

@extends('admin.master')

@section('content')


<!--
******************************************************************************
CRUD Menu
******************************************************************************
-->
<h1>{{{$title}}}</h1>
<br>

<!-- Show the form -->
{{ Form::open(array('class' => 'form-horizontal')) }}

    <!-- Bind to model -->
    
    <?php if (isset($menu)) echo Form::model($menu) ?>
    
    
    <div class="form-group">
        {{ Form::label('fk_MealType', 'Meal Type', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ @Form::select('fk_MealType', $mealModesAr, null, array('class' => 'form-control')); }}</div>
    </div>
    
    <div class="form-group">
        {{ Form::label('menu_type', 'Type of Menu', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ @Form::select('menu_type', array('custom' => 'custom', 'fixed' => 'fixed'), null, array('class' => 'form-control')); }}</div>
    </div>

    <div class="form-group">
        {{ Form::label('bgimg', 'CDN Image URL', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('bgimg', null, array('class'=>'form-control', 'required'=>true)) }}</div>
    </div>
    <div class="form-group"><img src="{{{@$menu->bgimg}}}"></div>
    
    <div class="form-group">
        {{ Form::label('name', 'Menu Name', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('name', null, array('class'=>'form-control')) }}</div>
    </div>
    
    <div class="form-group">
        {{ Form::label('for_date', 'For Date', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('for_date', null, array('class'=>'form-control', 'placeholder'=>'yyyy-mm-dd', 'required'=>true)) }}</div>
    </div>
    
    <div class="form-group">
        {{ Form::label('oa', 'Available for Order Ahead?', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::checkbox('oa', 1, null, array('style' => 'width:30px; height:30px;')); }}</div>
    </div>
    
    <div class="form-group">
        {{ Form::label('i_notes', 'Internal Notes', array('class' => 'col-sm-2 control-label label-light')) }}
        <div class="col-sm-5">{{ Form::textarea('i_notes', null, array('class' => 'form-control')) }}</div>
    </div>
            
    <hr>
    <h3>Mains & Sides on this menu</h3>

    @include('admin.dish.partials.list', array('list' => $dishesAll, 'checked' => $dishesInMenu))
    
    <hr>
    <h3>Add-ons on this menu</h3>
    
    @include('admin.dish.partials.list', array('list' => $addonsAll, 'checked' => $addonsInMenu))
    
    <div class="form-group">
      <div><button type="submit" class="btn btn-success pull-right">Save</button></div>
    </div>
    
    
{{ Form::close() }}



  
@stop