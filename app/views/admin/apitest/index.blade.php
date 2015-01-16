@extends('admin.master')


@section('content')


Key: <span class="label label-success">Public</span> <span class="label label-primary">Private</span>
<br>
api_token: {{{Session::get('api_token')}}}

<h1>Orders</h1>
 
    <form action="/order/phase1" method="post" class="admin-jsonForm" enctype='application/json'>
      <h3><span class="label label-primary">&nbsp;</span> POST: /order/phase1</h3>
      <br>
      <b>Returns:</b><br>
      <ul>
        <li><code>200</code> if ok. <br>
          {"reserveId":5}<br></li>
        <li><code>400</code> if pending order exists. <br>
          {"Error":"A pending order already exists for you."}<br></li>
        <li><code>410</code> if the inventory is not available. <br>
          {"Error":"Some of our inventory just sold out!"}<br>
          <mark>At this point, you should call <code>/status/menu</code> again and
            refresh which items are out of stock!</mark></li>
      </ul>
      <br>
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "order": [
        {"itemId": 2},
        {"itemId": 2}
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