@extends('admin.master')


@section('content')

<form method="post">
    <?php
    foreach ($iosCopy as $row) {
        if ($row->type == 'textarea') {
            ?>
            {{{$row->key}}}: <br>
            <textarea name="ioscopy[{{{$row->key}}}]" class="form-control">{{{$row->value}}}</textarea><br><br>
            <?php
        }
        else {
            ?>
            {{{$row->key}}}: <br>
            <input type="text" class="form-control" name="ioscopy[{{{$row->key}}}]" value="{{{$row->value}}}"><br><br>
            <?php
        }
    }
    ?>
            
    <button type="submit" class="btn btn-success">Save</button>
</form>
    
@stop