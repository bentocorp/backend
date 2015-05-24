@extends('admin.master')


@section('content')


Key: <span class="label label-success">Public</span> <span class="label label-primary">Private</span>
<br>
api_token: {{{Session::get('api_token')}}}


<!--
******************************************************************************
Status
******************************************************************************
-->
<hr>
<h1>Init & Status</h1>

    <h3>GET:</h3>
    
    <p>
        <span class="label label-success">&nbsp;</span> <a href="/init">/init/{date?}</a><br>

        Includes calls:
        <ul>
            <li><code>/status/overall</code>,
            <li><code>/status/all</code>,
            <li><code>/ioscopy</code>, 
            <li>and <code>/servicearea</code>.<br>
            <li>IF {date} is included, then the keys <code>/menu/{date}</code> and <code>/menu/next/{date}</code> 
                are added, with calls made to the supplied date. If there is no menu for a call, the value is <code>null</code>.<br>
            <li>Includes other data as well.
        </ul>
    </p>
    
    <p>
      <span class="label label-success">&nbsp;</span> <a href="/status/overall">/status/overall</a><br>
      &nbsp; { "value": "open" | "closed" | "sold out" }
    </p>

    <p><span class="label label-success">&nbsp;</span> <a href="/status/menu">/status/menu</a></p>
      
    <p>
      <span class="label label-success">&nbsp;</span> <a href="/status/all">/status/all</a><br>
      &nbsp; { "menu": [/status/menu array], "overall": "same as /status/overall" }
    </p>
    
    
<!--
******************************************************************************
Order
******************************************************************************
-->
<hr>
<h1>Order</h1>
 
    <!--
    <b>A reminder about the flow:</b><br>
    <p>
    The user makes their bento box, and wants to pay. BEFORE you process their payment on stripe,
    you have to send the order to /order/phase1. I will check the inventory, make sure I have it, 
    and reserve the inventory for that user. If all that is okay, I return 200. Then you process
    their payment, and send me the confirmation to /order/phase2. Then I dispatch the order.
    </p>
    -->
    


    <!-- <div class="admin-jsonForm"> -->
    <h3><span class="label label-primary">&nbsp;</span> 
      POST: /order</h3>

    <br>
    <b>Returns:</b><br>
    <ul>
      <li><code>200</code> if ok.</li>
      <li><code>402</code> if No payment specified, and no payment on file.<br></li>
      <li><code>410</code> if the inventory is not available. The UI
        should be updated, and the return includes the inventory:<br>
        <pre>
{
    "Error":"Some of our inventory just sold out!",
    "MenuStatus:" [same array as /status/menu]  
}
        </pre>
      </li>
      <li><code>406</code> if payment failed.</li>
      <li><code>423</code> if the restaurant is not open.</li>
    </ul>

    <form action="/order" method="post">
      data: (an example with two CustomerBentoBox)<br>
      <b>NOTE: <code>stripeToken</code></b> must always be present. If the user is 
        just using their existing card, set this to NULL.</b><br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "OrderItems": [
        {
            "item_type": "CustomerBentoBox",
            "items": [
                {"id": 11,  "type": "main"}, 
                {"id": 1,  "type": "side1"},
                {"id": 1,  "type": "side2"},
                {"id": 5,  "type": "side3"}, 
                {"id": 3, "type": "side4"}
            ]
        },
        {
            "item_type": "CustomerBentoBox",
            "items": [
                {"id": 9,  "type": "main"}, 
                {"id": 5,  "type": "side1"}, 
                {"id": 4, "type": "side2"},
                {"id": 6,  "type": "side3"},
                {"id": 5,  "type": "side4"} 
            ]
        }
    ],
    "OrderDetails": {
        "address": {
            "number": "1111",
            "street": "Kearny st.",
            "city": "San Francisco",
            "state": "CA",
            "zip": "94133"
        },
        "coords": {
            "lat": "37.798220",
            "long": "-122.405606"
        },
        "tax_cents": 137,
        "tip_cents": 200,
        "total_cents": "1537"
    },
    "Stripe": {
        "stripeToken": "tok_15Mt2kEmZcPNENoGjJw2am8L"
    },
    "CouponCode":"bentoyum26"
}
      </textarea>
      <input type="hidden" name="api_token" value="{{{Session::get('api_token')}}}">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <!-- </div> -->

    
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
User 
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
      <li><code>409</code> if email already exists.</li>
    </ul>

    <form action="/user/signup" method="post">
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "name": "John J. Smith",
    "email": "vincent+5@bentonow.com",
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
    <b>Returns:</b>
    <ul>
      <li>Same as /user/signup, plus:</li>
      <li><code>403</code> if bad fb_token.</li>
    </ul>
    <br>

    <form action="/user/fbsignup" method="post">
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "firstname": "John",
    "lastname": "Smith",
    "email": "vincent+4@bentonow.com",
    "phone": "555-123-4567",
    "fb_id": "someid",
    "fb_token": "CAALQCVl1AkkBAMz3ycA3l4mkNvBqVu0y5qjh1dhZARrjqTitqyZAl62z77I80AZAqoXC8BF3E47wZBIeH2rte11QU0LRl7eOZBHk7ZAVZCNpHcbmJtIKHkzLtrL4pYkmoKW9t1cjLxZAhqNqwjZBrLgZAlkwzcU4dSut8PtlRpeafXaZBv9YUyrs30MOEW4t1Tp381A7qqUpHCu4MmZCH4qmQfan",
    "fb_profile_pic": "http://profilepic.jpg",
    "fb_age_range": "some range",
    "fb_gender": "male"
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
      <li><code>200</code> if ok.</li>
      <li><code>404</code> if email not found</li>
      <li><code>403</code> if bad password</li>
    </ul>

    <form action="/user/login" method="post">
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "email": "vincent+5@bentonow.com",
    "password": "somepassword716*"
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
      <li><code>200</code> if ok.</li>
      <li><code>404</code> if email not found</li>
      <li><code>403</code> if bad fb_token</li>
    </ul>

    <form action="/user/fblogin" method="post">
      data:<br>
      note: existing fb_token in DB = myfbtoken<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "email": "vincent+4@bentonow.com",
    "fb_token": "myfbtoken"
}
      </textarea>
      <input type="hidden" name="api_token" value="{{{Session::get('api_token')}}}">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>


    <h3>GET:</h3>
    <ul>
      <li><span class="label label-primary">&nbsp;</span> <a href="/user/logout">/user/logout</a><br>
        You must include the <code>api_token</code> as per usual.<br>
        <b>return:</b> <br>
        <code>200</code> if ok <br>
        <code>404</code> if api_token not found <br>
        <code>403</code> if no api_token given <br>
      </li>
      
      <li><span class="label label-primary">&nbsp;</span> <a href="/user/info">/user/info</a><br>
        Returns the user's info. You must include the <code>api_token</code> as per usual.<br>
        <b>return:</b> <br>
        <code>200</code> if ok <br>
      </li>
    </ul>
    
    
<!--
******************************************************************************
Coupon
******************************************************************************
-->
<hr>
<h1>Coupon</h1>
    
    <h3>GET:</h3>
    <ul>
      <li><span class="label label-primary">&nbsp;</span> 
        <a href="/coupon/apply/{code}">/coupon/apply/{code}</a><br>
        All are invalid for now, except for 1121113370998kkk7<br>
        <b>return:</b> <br>
        <code>200</code> if ok <br>
        <code>400</code> if invalid coupon <br>
      </li>
    </ul>
    
    
    <h3><span class="label label-success">&nbsp;</span> <span class="label label-primary">&nbsp;</span>
      POST: /coupon/request</h3>
    
    <br>
    <div><b>NOTE:</b> Send <code>api_token</code> if the user is logged in.</div>
    <b>Returns:</b><br>
    <ul>
      <li><code>200</code> if ok.</li>
    </ul>

    <form action="/coupon/request" method="post">
      data:<br>
      <textarea name="data" class="form-control admin-jsonTextarea">
{
    "reason": "sold out",
    "email": "me@me.com"
}
      </textarea>
      <input type="hidden" name="api_token" value="{{{Session::get('api_token')}}}">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>
    

@stop