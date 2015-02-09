<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bento Admin</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    
    <!-- Everything else -->
    <link rel="stylesheet" href="/css/main.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>

  <div class="container">
    <br>
    
    <div class="row">
      <div class="col-lg-12">
        <nav class="navbar navbar-default">
          <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
              <a class="navbar-brand" href="/admin">Bento Admin</a>
            </div>
            <div class="collapse navbar-collapse">
              <p class="navbar-text navbar-right">{{{ $user->name }}} 
                (<a href="/admin/logout" class="navbar-link">Logout</a>) 
                <?php
                  if (Session::has('api_token')) {
                      $fake = Session::get('api_impersonating');
                      echo "<br><small>(Impersonating $fake->email $fake->pk_User) [<a href='/admin/user/logout' class='navbar-link'>Logout</a>]</small>";
                  }
                  ?>
              </p>
            </div>
          </div>
        </nav>
        
        
      </div>
    </div>

    <div class="row">
        <div class="col-lg-2">
          <div id="sidebar">
          
            <ul class="nav nav-pills nav-stacked">
              <li role="presentation"><a href="/admin">Dashboard</a></li>
            </ul>
            
            <h4>Service</h4>
            <ul class="nav nav-pills nav-stacked">
              <li role="presentation"><a href="/admin/inventory">Inventory</a></li>
              <li role="presentation"><a href="/admin/order">Orders</a></li>
              <li role="presentation"><a href="/admin/pendingorder">Pending Orders</a></li>
            </ul>

            <h4>Kitchen</h4>
            <ul class="nav nav-pills nav-stacked">
              <li role="presentation"><a href="/admin/menu">Menus</a></li>
              <li role="presentation"><a href="/admin/dish">Dishes</a></li>
              <!-- <li role="presentation"><a href="/admin/preplog">Prep Log</a></li> -->
            </ul>
            
            <h4>Business</h4>
            <ul class="nav nav-pills nav-stacked">
              <li role="presentation"><a href="/admin/driver">Drivers</a></li>
              <li role="presentation"><a href="/admin/user">Users</a></li>
              <li role="presentation"><a href="/admin/coupon">Coupons</a></li>
            </ul>

            <h4>Dev</h4>
            <ul class="nav nav-pills nav-stacked">
              <li role="presentation"><a href="/admin/apitest">API Tests</a></li>
              <li role="presentation"><a href="/admin/misc/ioscopy">iOS Copy</a></li>
              <li role="presentation"><a href="/admin/settings">Settings</a></li>
            </ul>
          
          </div>
        </div>
        <div class="col-lg-10">
          <?php
            if (Session::has('msg')) {
                $msg = Session::get('msg');
                echo "<div class='alert alert-{$msg['type']}' role='alert'>{$msg['txt']}</div>";
            }
            ?>
          
          @yield('content')
        </div>
    </div>
    
  </div><!-- /container -->
    
    <footer class="footer">
        <div class="container">
          <p class="text-muted">&copy; Bento Technology, Inc</p>
        </div>
    </footer>
      
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/js/bootstrap.min.js"></script>
    
  </body>
</html>