<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>

<?php


echo "<h2>Stack Trace:</h2>";
echo "<pre>";
echo $e;
echo "</pre>";


echo "<h2>User:</h2>";
echo "<pre>";
var_dump ($user);
echo "</pre>";


echo '<h2>$_SERVER:</h2>';
echo "<pre>";
echo "REQUEST_URI: {$_SERVER['REQUEST_URI']}";
echo "</pre>";


echo '<h2>$_REQUEST:</h2>';
echo "<pre>";
var_dump ($_REQUEST);
echo "</pre>";


?>
  
  
</body>
</html>
