<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
</head>
<body>
<? 
include './blog.php';
$blog = blog::getInstance();
$blog->affiche();
?>
</body>
</html>
