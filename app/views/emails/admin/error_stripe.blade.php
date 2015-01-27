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

#echo "<pre>$e->getTraceAsString()</pre>";

?>
  
  
</body>
</html>
