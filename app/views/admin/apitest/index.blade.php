@extends('admin.master')


@section('content')


Key: <span class="label label-success">Public</span> <span class="label label-primary">Private</span>
<br>
api_token: {{{Session::get('api_token')}}}


<!--
******************************************************************************
Order
******************************************************************************
-->
<hr>
<h1>Order</h1>
 
    <b>A reminder about the flow:</b><br>
    <p>
    The user makes their bento box, and wants to pay. BEFORE you process their payment on stripe,
    you have to send the order to /order/phase1. I will check the inventory, make sure I have it, 
    and reserve the inventory for that user. If all that is okay, I return 200. Then you process
    their payment, and send me the confirmation to /order/phase2. Then I dispatch the order.
    </p>
    
    <br>

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

    <form action="/order/phase1" method="post">
      data: (an example with two CustomerBentoBox)<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "OrderItems": [
        {
            "item_type": "CustomerBentoBox",
            "items": [
                {"id": 3,  "type": "main"}, 
                {"id": 1,  "type": "side1"},
                {"id": 1,  "type": "side2"},
                {"id": 5,  "type": "side3"}, 
                {"id": 11, "type": "side4"}
            ]
        },
        {
            "item_type": "CustomerBentoBox",
            "items": [
                {"id": 4,  "type": "main"}, 
                {"id": 5,  "type": "side1"}, 
                {"id": 11, "type": "side2"},
                {"id": 6,  "type": "side3"},
                {"id": 5,  "type": "side4"} 
            ]
        }
    ],
    "OrderDetails": {
        "address": {
            "street": "1111 Kearny st.",
            "city": "San Francisco",
            "state": "CA",
            "zip": "94133"
        },
        "coords": {
            "lat": "37.798220",
            "long": "-122.405606"
        }
    }
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

    <form action="/order/phase2" method="post">
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "pendingOrderId": 4,
    "stripe_chargeId": "ch_15NBUFEmZcPNENoGNKwyB7C9"
}
      </textarea>
      <input type="hidden" name="api_token" value="{{{Session::get('api_token')}}}">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>
    

<!--
******************************************************************************
Menu
******************************************************************************
-->
<hr>
<h1>Menu</h1>
    
    <h3>GET:</h3>
    <ul>
      <li><span class="label label-success">&nbsp;</span> <a href="/menu/20150107">/menu/20150107</a><br>
        return: 200 | 404</li>
    </ul>

    
<!--
******************************************************************************
Status
******************************************************************************
-->
<hr>
<h1>Status</h1>

    <h3>GET:</h3>
    <ul>
      <li><span class="label label-success">&nbsp;</span> <a href="/status/overall">/status/overall</a></li>
      <li><span class="label label-success">&nbsp;</span> <a href="/status/menu">/status/menu</a></li>
    </ul>
    
    
<!--
******************************************************************************
Misc
******************************************************************************
-->
<hr>
<h1>Misc</h1>
    
    <h3>GET:</h3>
    <ul>
      <li><span class="label label-success">&nbsp;</span> <a href="/ioscopy">/ioscopy</a></li>
    </ul>
    

<!--
******************************************************************************
User Login/Registration
******************************************************************************
-->
<hr>
<h1>User</h1>
 
    <!-- <div class="admin-jsonForm"> -->
    <h3><span class="label label-primary">&nbsp;</span> 
      POST: /user/signup</h3>

    <br>
    <b>Returns:</b><br>
    <ul>
      <li><code>200</code> if ok. <br>
        {"api_token": "some_long_string"}<br></li>
      <li><code>400</code> if errors. An array of error messages, e.g.:<br>
        [
            "The email has already been taken.",
            "The password must be at least 8 characters."
        ]
      </li>
    </ul>

    <form action="/user/signup" method="post">
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "name": "John J. Smith",
    "email": "test1@bentonow.com",
    "phone": "555-123-4567",
    "password": "somepassword"
}
      </textarea>
      <input type="hidden" name="api_token" value="{{{Session::get('api_token')}}}">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <!-- </div> -->


    <h3><span class="label label-primary">&nbsp;</span>
      POST: /user/fbsignup</h3>
    
    <br>
    <b>Returns:</b><br>
    Same as /user/signup
    <br>

    <form action="/user/fbsignup" method="post">
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "firstname": "John",
    "lastname": "Smith",
    "email": "test1@bentonow.com",
    "phone": "555-123-4567",
    "fb_id": "someid",
    "fb_profile_pic": "http://profilepic.jpg"
}
      </textarea>
      <input type="hidden" name="api_token" value="{{{Session::get('api_token')}}}">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>
    
    
    <h3><span class="label label-primary">&nbsp;</span>
      POST: /user/login</h3>
    
    <br>
    <b>Returns:</b><br>
    <ul>
      <li><code>200</code> if ok. <br>
        <pre>
{
    "email": "test2@bentonow.com",
    "phone": "555-123-4567",
    "api_token": "somelongtoken"
}
        </pre>
      </li>
      <li><code>404</code> if email not found</li>
      <li><code>403</code> if bad password</li>
    </ul>

    <form action="/user/login" method="post">
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "email": "test2@bentonow.com",
    "password": "somepassword"
}
      </textarea>
      <input type="hidden" name="api_token" value="{{{Session::get('api_token')}}}">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>
    
    
    <h3><span class="label label-primary">&nbsp;</span>
      POST: /user/fblogin</h3>
    
    <br>
    <b>Returns:</b><br>
    <ul>
      <li><code>200</code> if ok. <br>
        <pre>
{
    "email": "test1@bentonow.com",
    "phone": "555-123-4567",
    "api_token": "somelongtoken"
}
        </pre>
      </li>
      <li><code>404</code> if email not found</li>
      <li><code>403</code> if bad fb_id</li>
    </ul>

    <form action="/user/fblogin" method="post">
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "email": "test1@bentonow.com",
    "fb_id": "someid"
}
      </textarea>
      <input type="hidden" name="api_token" value="{{{Session::get('api_token')}}}">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>



@stop