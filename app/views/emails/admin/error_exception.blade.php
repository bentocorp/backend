<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>

<?php

echo "<div><b>Error UUID:</b><br>";
echo "<pre>";
echo $uuid;
echo "</pre></div><br>";


echo "<div><b>Subject:</b><br>";
echo "<pre>";
echo $subject;
echo "</pre></div><br>";


echo "<div><b>Message:</b><br>";
echo "<pre>";
echo $msg;
echo "</pre></div><br>";


echo "<h2>Stack Trace:</h2>";
echo "<pre>";
echo $e;
echo "</pre>";


echo "<h2>User:</h2>";
echo "<pre>";
var_dump ($user);
echo "</pre>";


echo "<h2>Admin User:</h2>";
echo "<pre>";
var_dump ($adminUser);
echo "</pre>";


echo '<h2>$_SERVER:</h2>';
echo "<pre>";
$request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'not set (maybe you are unit testing)';
echo "REQUEST_METHOD: $request_method <br>";

$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'not set (maybe you are unit testing)';
echo "REQUEST_URI: $request_uri <br>";
echo "</pre>";


echo '<h2>$_REQUEST:</h2>';
echo "<pre>";
var_dump ($_REQUEST);
echo "</pre>";


echo '<h2>$_POST:</h2>';
echo "<pre>";
var_dump ($_POST);
echo "</pre>";


?>
  
  
</body>
</html>
