

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bento helps you reset your password</title>

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

          <h1>Reset your password <small><br>It's ok. It happens. Bento is here to help!</small></h1>

          <?php
          if (Session::has('status')) {
              $msg = Session::get('status');
              echo "<div class='alert alert-success' role='alert'><b>$msg</b></div>";
          }

          if (Session::has('error')) {
              $msg = Session::get('error');
              echo "<div class='alert alert-danger' role='alert'><b>$msg</b></div>";
          }
          ?>

          <form action="/password/remind" method="POST">
              <input type="email" name="email" placeholder="Just enter your email..." class="form-control" required>
              <br>
              <input type="submit" value="Email My Reset Link" class="btn btn-success">
          </form>

        </div>
    </div>
    
  </div><!-- /container -->
  
  </body>
</html>

