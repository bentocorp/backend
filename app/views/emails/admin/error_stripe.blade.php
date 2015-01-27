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

echo "<p><b>User:</b></p>";
echo "<pre>";
var_dump($user);
echo "</pre>";

?>
  
  
</body>
</html>
