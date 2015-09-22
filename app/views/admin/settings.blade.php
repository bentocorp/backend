@extends('admin.master')

@section('content')

<h2>Settings</h2>

<hr>
<h3>Service Area KML</h3>
<p>Format: Four or more tuples, each consisting of floating point values for <code>longitude,latitude,altitude</code>. 
  Space delimited. Self-closing (the first point is the same as the last point).<br>
  Source: https://developers.google.com/kml/documentation/kmlreference#coordinates</p>

<!-- Lunch KML -->
<form method="post" action="/admin/settings/save-setting">
    <b>Lunch Service Area KML</b><br>
    <textarea name="value" class="form-control">{{$settings['serviceArea_lunch']}}</textarea>
    
    <input type="hidden" name="key" value="serviceArea_lunch">
    <button type="submit" class="btn btn-success">Save</button>
</form>

<br>

<!-- Dinner KML -->
<form method="post" action="/admin/settings/save-setting">   
    <b>Dinner Service Area KML</b><br>
    <textarea name="value" class="form-control">{{$settings['serviceArea_dinner']}}</textarea>
    
    <input type="hidden" name="key" value="serviceArea_dinner">
    <button type="submit" class="btn btn-success">Save</button>
</form>
    
@stop