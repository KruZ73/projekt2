<?php

require("./../src/config.php");

use Steampixel\Route;

Route::add('/' , function() {
    global $twig;
    $posts = Post::getPage();
    $t = array("posts" => $posts);
    $twig->display("index.html.twig", $t);
});

Route::add('/upload' , function() {
    global $twig;
    $twig->display("upload.html.twig");
});

Route::add('/upload', function() {
    global $twig;

    $tempFileName = $_FILES['uploadFile']['tmp_name'];
    $title = $_POST['title'];
    Post::upload($tempFileName, $title);

    $twig->display("index.html.twig");
}, 'post');



Route::run('/projekt2/pub');



/*
require('./../src/config.php');

?>

<form action="" method="post" enctype="multipart/form-data">
        <label for="uploadedFileInput">
            Wybierz plik do wgrania na serwer:
        </label><br>
        <input type="file" name="uploadedFile" id="uploadedFileInput" required><br>
        <input type="submit" value="Wyślij plik" name="submit"><br>
</form>

<?php
    //sprawdź czy został wysłany formularz
    if(isset($_POST['submit']))  {
        Post::upload($_FILES['uploadedFile']['tmp_name']);
    }
?>

Ostatni post:
<pre>
<?php
var_dump(Post::getPage());

*/







?>