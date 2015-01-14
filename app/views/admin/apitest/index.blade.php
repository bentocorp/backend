@extends('admin.master')


@section('content')


Key: <span class="label label-success">Public</span> <span class="label label-primary">Private</span>
<br>
api_token: {{{Session::get('api_token')}}}

<h1>Orders</h1>
 
    <form action="/order/phase1" method="post" class="admin-jsonForm">
      <h3><span class="label label-primary">&nbsp;</span> POST: /order/phase1</h3>
      
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    otherAttrs: foobar,
    order: [
        {itemId: qty},
        {itemId: qty}
    ]
}
      </textarea>
      <input type="hidden" name="api_token" value="{{{Session::get('api_token')}}}">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>


    <h3>POST: /order/phase2</h3>
    
    
<h1>Menu</h1>
    
    <h3>GET:</h3>
    <ul>
      <li><span class="label label-success">&nbsp;</span> <a href="/menu/20150107">/menu/20150107</a></li>
    </ul>

<h1>Status</h1>

    <h3>GET:</h3>
    <ul>
      <li><span class="label label-success">&nbsp;</span> <a href="/status/overall">/status/overall</a></li>
      <li><span class="label label-success">&nbsp;</span> <a href="/status/menu">/status/menu</a></li>
    </ul>


@stop