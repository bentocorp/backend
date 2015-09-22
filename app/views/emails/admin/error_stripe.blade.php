<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
</head>
<body>

<?php
$body = $e->getJsonBody();
$err  = $body['error'];

print('Status is: ' . $e->getHttpStatus() . "<br>");
print('Type is: ' . $err['type'] . "<br>");
print('Message is: ' . $err['message'] . "<br>");

echo "<h2>Stack Trace:</h2>";
echo "<pre>";
echo $e;
echo "</pre>";

echo "<h2>User:</h2>";
echo "<pre>";
var_dump($user);
echo "</pre>";

?>
  
  
</body>
</html>
