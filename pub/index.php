<?php

require("./../src/config.php");

use Steampixel\Route;

Route::add('/' , function() {
    global $twig;
    $twig->display("index.html.twig");
});

Route::add('/upload' , function() {
    global $twig;
    $twig->display("upload.html.twig");
});

Route::add('/upload', function(){
    global $twig;

    $tempFileName = $_POST['uploadedFile']['tmp_name'];
    Post::upload($tempFileName);

    $twig->display("index.html.twig");
}, 'post');



Route::run('/projekt2/pub');
?>