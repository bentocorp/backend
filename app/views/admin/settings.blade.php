@extends('admin.master')

@section('content')

<h2>Settings</h2>

<form method="post" action="/admin/settings/save-setting">
    
    <b>Service Area KML</b><br>
    Format: Four or more tuples, each consisting of floating point values for longitude,latitude,altitude. Space delimited.<br>
    Source: https://developers.google.com/kml/documentation/kmlreference#coordinates
    <textarea name="value" class="form-control">{{{$settings['serviceArea']}}}</textarea>
    
    <input type="hidden" name="key" value="serviceArea">
    <button type="submit" class="btn btn-success">Save</button>
</form>
    
@stop