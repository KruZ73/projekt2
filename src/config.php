<?php

require("./../vendor/autoload.php");

$db = new mysqli("localhost", "root", "", "img");
require("Post.class.php");

$loader = new Twig\Loader\FilesystemLoader('./../src/templates');

$twig = new Twig\Environment($loader);


?>