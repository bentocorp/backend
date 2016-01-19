

@extends('admin.master')

@section('content')


<script>
/***************************************
 * On document ready
 */
 $(document).ready(function() {

    var oaChecked = $('#oa_avail').is(':checked');

    // Handle the required qty field for Order Ahead
    function oaChange() 
    {
        oaChecked = $('#oa_avail').is(':checked');
        //console.log('oaChange');
        
        // Required OA fields 
        if (oaChecked) {
            $("#menu-list tr").each(function()
            {
                // Require qty for things on the menu
                //console.log('Analyzing a row');
                // Only for things on the menu
                var onmenu = $(this).find(".dish-onmenu").is(':checked');
                var input = $(this).find(".menu-oa-qty-in");
                
                if (onmenu) {
                    //console.log('here3');
                    input.removeAttr('disabled');
                }
                else
                    input.attr('disabled', true);
                
                // Require OA time
                $("#oa_times").show();
                $("#oa_times input[name='oa_times']").removeAttr('disabled');
            });
        }
        // Else remove for all
        else {
            // Un-Require qty for things on the menu
            //console.log('Clearing all');
            $(".menu-oa-qty-in").attr('disabled', true);
            
            // Un-Require OA time
            $("#oa_times").hide();
            $("#oa_times input[name='oa_times']").attr('disabled', true);
        }
       
    }
    
    
    // Handle the required qty field for Order Ahead
    function dishOnMenuChange() 
    {
        if (!oaChecked)
            return;
        
        var checked = $(this).is(':checked');
        var input = $(this).parents('tr').find('.menu-oa-qty-in');

        // Require price for mains and add-ons
        if (checked) {
            input.removeAttr('disabled');
        }
        // Else remove for all
        else {
            input.attr('disabled', true);
        }
       
    }
              
    // Order Ahead menu checkbox change
    $('#oa_avail').on('keyup blur change', oaChange);
    // init
    oaChange();
              
    // Changing an individual dish being on/off the menu
    $('.dish-onmenu').on('keyup blur change', dishOnMenuChange);
            
});
</script>

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
        {{ Form::hidden('od_avail', 0) }}
        {{ Form::label('od_avail', 'Menu Available for On-Demand?', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::checkbox('od_avail', 1, null, array('style' => 'width:30px; height:30px;')); }}</div>
    </div>
    
    <div class="form-group">
        {{ Form::hidden('oa_avail', 0) }}
        {{ Form::label('oa_avail', 'Menu Available for Order Ahead?', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">{{ Form::checkbox('oa_avail', 1, null, array('style' => 'width:30px; height:30px;')); }}</div>
    </div>
    
    <div class="form-group" id="oa_times">
        {{ Form::label('oa_times', 'Time Slots', array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-5">
          <p>Lunch: {{ Form::radio('oa_times', '11:00-11:30,11:30-12:00,12:00-12:30,12:30-13:00,13:00-13:30,13:30-14:00', null, array('class' => '', 'required'=>true)); }} 11:00-11:30,11:30-12:00,12:00-12:30,12:30-13:00,13:00-13:30,13:30-14:00<p>
          <p>Dinner: {{ Form::radio('oa_times', '17:00-18:00,18:00-19:00,19:00-20:00,20:00-21:00', null, array('class' => '', 'required'=>true)); }} 17:00-18:00,18:00-19:00,19:00-20:00,20:00-21:00</p>
        </div>
    </div>
    
    <div class="form-group">
        {{ Form::label('i_notes', 'Internal Notes', array('class' => 'col-sm-2 control-label label-light')) }}
        <div class="col-sm-5">{{ Form::textarea('i_notes', null, array('class' => 'form-control')) }}</div>
    </div>
            
    <hr>
    <h3>Mains & Sides on this menu</h3>

    @include('admin.dish.partials.list', array('list' => $dishesAll, 'checked' => $dishesInMenu, 'mode' => 'crud'))
    
    <hr>
    <h3>Add-ons on this menu</h3>
    
    @include('admin.dish.partials.list', array('list' => $addonsAll, 'checked' => $addonsInMenu, 'mode' => 'crud'))
    
    <div class="form-group">
      <div><button type="submit" class="btn btn-success pull-right" tabindex="10">Save</button></div>
    </div>
    
    
{{ Form::close() }}



  
@stop