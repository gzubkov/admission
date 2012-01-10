<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html>
<head>
<title>LaTeX Equations and Graphics in PHP</title>
</head>

<body>

<!-- form to enter LaTeX code -->
<form action="render_example.php" method="post">
<textarea rows="20"
          cols="60"
          name="render_text"><?=stripslashes($_POST['render_text']);?></textarea><br />
<input name="submit"
       type="submit"
       value="Render" />
</form>

<?php

if (isset($_POST['submit'])) {
   echo '<h1>Result</h1>';

   require('render.class.php');

   $text = "[tex]".$_POST['render_text']."[/tex]";

   if (get_magic_quotes_gpc())
      $text = stripslashes($text);

   $render = new render();
   echo $render->transform($text);

}
?>

</body>
</html>
