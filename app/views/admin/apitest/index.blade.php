@extends('admin.master')


@section('content')


Key: <span class="label label-success">Public</span> <span class="label label-primary">Private</span>
<br>
api_token: {{{Session::get('api_token')}}}

<h1>Orders</h1>
 
    <!-- <div class="admin-jsonForm"> -->
    <h3><span class="label label-primary">&nbsp;</span> 
      POST: /order/phase1</h3>

    <br>
    <b>Returns:</b><br>
    <ul>
      <li><code>200</code> if ok. <br>
        {"reserveId":5}<br></li>
      <li><code>400</code> if pending order exists. <br>
        {"Error":"A pending order already exists for you."}<br></li>
      <li><code>410</code> if the inventory is not available. The UI
        should be updated, and the return includes the inventory:<br>
        <pre>
{
    "Error":"Some of our inventory just sold out!",
    "MenuStatus:" [same array as /status/menu]  
}
        </pre>
      </li>
    </ul>
    <br>

    <form action="/order/phase1" method="post">
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "order": [
        {"id": 1, "qty": 2},
        {"id": 3, "qty": 2}
    ]
}
      </textarea>
      <input type="hidden" name="api_token" value="{{{Session::get('api_token')}}}">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <!-- </div> -->


    <h3><span class="label label-primary">&nbsp;</span>
      POST: /order/phase2</h3>
    
    <br>
    <b>Returns:</b><br>
    <ul>
      <li><code>200</code> if ok.</li>
      <li><code>404</code> if pending order not found. This should really never happen.</li>
      <li><code>400</code> if the payment wasn't verified. The UI needs to handle this somehow.</li>
    </ul>
    <br>

    <form action="/order/phase2" method="post">
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "pendingOrderId": 4,
    "orderDetails": 0
}
      </textarea>
      <input type="hidden" name="api_token" value="{{{Session::get('api_token')}}}">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>
    
    
<h1>Menu</h1>
    
    <h3>GET:</h3>
    <ul>
      <li><span class="label label-success">&nbsp;</span> <a href="/menu/20150107">/menu/20150107</a><br>
        return: 200 | 404</li>
    </ul>

<h1>Status</h1>

    <h3>GET:</h3>
    <ul>
      <li><span class="label label-success">&nbsp;</span> <a href="/status/overall">/status/overall</a></li>
      <li><span class="label label-success">&nbsp;</span> <a href="/status/menu">/status/menu</a></li>
    </ul>


@stop