

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset your Bento password</title>

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

          <h1>Select a new password</h1>

            <?php
            // Success, don't show form
            if (Session::has('success')) {
                $msg = Session::get('success');
                echo "<div class='alert alert-success' role='alert'><b>$msg</b></div>";
            }
            // Else show form
            else {
                // And show an error message if there's one
                if (Session::has('error')) {
                    $msg = Session::get('error');
                    echo "<div class='alert alert-danger' role='alert'><b>$msg</b></div>";
                }
            ?>
          
            <form action="/password/reset" method="POST">
                <input type="email" name="email" placeholder="your email" class="form-control" required>
                <br>
                <input type="password" name="password"  placeholder="your new password" class="form-control" required>
                <br>
                <input type="password" name="password_confirmation" placeholder="your new password again" class="form-control" required>
                <br>
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="submit" value="Reset Password" class="btn btn-default">
            </form>
            <?php 
            } ?>

        </div>
    </div>
    
  </div><!-- /container -->
  
  </body>
</html>

