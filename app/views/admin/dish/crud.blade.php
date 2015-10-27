

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

        if (dishType == 'main')
            $('#price').removeAttr('disabled');
        else {
            $('#price').val('');
            $('#price').attr('disabled', true); 
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
        <div class="col-sm-5">{{ @Form::select('type', array('main' => 'main', 'side' => 'side'), $dish->type, array('class' => 'form-control')); }}</div>
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
        {{ Form::label('max_per_order', 'Max Per Bento', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::text('max_per_order', $default_max, array('class' => 'form-control')) }}</div>
    </div>
        
    <div class="form-group">
        {{ Form::label('i_notes', 'Internal Notes', array('class' => 'col-sm-2 control-label', 'style' => 'font-weight:normal !important; font-style:italic;')) }}
        <div class="col-sm-5">{{ Form::textarea('i_notes', null, array('class' => 'form-control', 'rows' => 5)) }}</div>
    </div>
    
    <div class="form-group">
      <div class="col-sm-7"><button type="submit" class="btn btn-success pull-right">Save</button></div>
    </div>

{{ Form::close() }}



  
@stop