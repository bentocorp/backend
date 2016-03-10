

@extends('admin.master')

@section('content')

<script>
/***************************************
 * On document ready
 */
 $(document).ready(function() {

    // Handle the required price field for main dishes only
    function dishChange() 
    {
        var dishType = $('#type').val();

        // Require price for mains and add-ons
        if (dishType == 'main' || dishType == 'addon')
            $('#price').removeAttr('disabled');
        else {
            $('#price').val('');
            $('#price').attr('disabled', true); 
       }
       
       // Don't need max per bento for add-ons
        if (dishType != 'addon')
            $('#max_per_order').removeAttr('disabled');
        else {
            $('#max_per_order').val('99');
            $('#max_per_order').attr('disabled', true); 
       }
    }
    
    dishChange();
            
    $('#type').on('keyup blur change', dishChange);

});
</script>

<!--
******************************************************************************
CRUD Dish
******************************************************************************
-->
<h1>{{{$title}}} <?php if ($mode != 'create'):?> <a class="btn btn-default pull-right" href="/admin/dish/create" role="button">+ New Dish</a> <?php endif; ?></h1>
<br>

<!-- Show the form -->
{{ Form::open(array('class' => 'form-horizontal')) }}

    <!-- Bind to model -->
    
    <?php if (isset($dish)) echo Form::model($dish) ?>

    <div class="form-group">
        {{ Form::label('image1', 'CDN Image URL', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('image1', null, array('class' => 'form-control')) }}</div>
    </div>
    <div>CDN Image:</div>
    <div class="form-group"><img src="{{{@$dish->image1}}}"></div>
    
    <hr class="hr-bento">
    
    <div class="form-group">
        {{ Form::label('email_image1', 'CDN Email Image URL', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('email_image1', null, array('class' => 'form-control')) }}</div>
    </div>
    <div>CDN Email Image:</div>
    <div class="form-group"><img src="{{{@$dish->email_image1}}}"></div>
    
    <div class="form-group">
        {{ Form::label('name', 'Dish Name', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('name', null, array('class'=>'form-control')) }}</div>
    </div>
    
    <!--
    <div class="form-group">
        {{ Form::label('short_name', 'Short Name', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('short_name', null, array('class' => 'form-control')) }}</div>
    </div>
    -->
        
    <div class="form-group">
        {{ Form::label('description', 'Savory Description', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::textarea('description', null, array('class' => 'form-control', 'rows' => 3)) }}</div>
    </div>
        
    <div class="form-group">
        {{ Form::label('type', 'Type', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ @Form::select('type', array('main' => 'main', 'side' => 'side', 'addon' => 'addon'), $dish->type, array('class' => 'form-control')); }}</div>
    </div>
    
    <div class="form-group">
        {{ Form::label('price', 'Price', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ @Form::number('price', $dish->price, array(
                    'class' => 'form-control',
                    'required' => 'required',
                    'min' => '1',
                    'step' => '.01',
                    'disabled' => 'disabled'
        ))}}</div>
    </div>
    
    <div class="form-group">
        {{ Form::label('label', 'Label', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('label', null, array(
                    'class' => 'form-control','required' => 'required'))}}</div>
    </div>
    
    <div class="form-group">
        {{ Form::label('temp', 'Temperature', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ @Form::select('temp', array('hot' => 'hot', 'cold' => 'cold'), $dish->temp, array('class' => 'form-control')); }}</div>
    </div>
    
    <div class="form-group">
        <?php @$default_max = $dish->max_per_order ? $dish->max_per_order : 99 ?>
        <!-- "Default" in Laravel is an *always* case. It will *always* populate with what you set,
              even if there is already a value in the DB. So we need additional logic when you want
              a default value *only when* the DB is empty -->
        {{ Form::label('max_per_order', 'Max Per Bento', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('max_per_order', $default_max, array('class' => 'form-control')) }}</div>
    </div>
        
    <div class="form-group">
        {{ Form::hidden('od_avail', 0) }}
        {{ Form::label('od_avail', 'Dish Available for On-Demand?', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::checkbox('od_avail', 1, null, array('style' => 'width:30px; height:30px;')); }}</div>
    </div>
    
    <div class="form-group">
        {{ Form::hidden('oa_avail', 0) }}
        {{ Form::label('oa_avail', 'Dish Available for Order Ahead?', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::checkbox('oa_avail', 1, null, array('style' => 'width:30px; height:30px;')); }}</div>
    </div>
    
    <hr>
    
    <div class="form-group">
        {{ Form::hidden('is_sushi', 0) }}
        {{ Form::label('is_sushi', 'For the Sushi Station?', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::checkbox('is_sushi', 1, null, array('style' => 'width:30px; height:30px;')); }}</div>
    </div>
    
    <div class="form-group">
        {{ Form::label('i_notes', 'Internal Notes', array('class' => 'col-sm-2 control-label label-light')) }}
        <div class="col-sm-5">{{ Form::textarea('i_notes', null, array('class' => 'form-control', 'rows' => 5)) }}</div>
    </div>
    
    <div class="form-group">
      <div class="col-sm-7"><button type="submit" class="btn btn-success pull-right">Save</button></div>
    </div>

{{ Form::close() }}



  
@stop