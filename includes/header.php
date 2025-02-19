<?php
require_once 'functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>I Got Chills | The latest horror news & reviews</title>
    <meta name="description" content="A brief description of the page" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Road+Rage&family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet" />
    <!-- Site CSS -->
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <nav>
        <a href="index.php" class="logo">I<span class="logo-accent">GOT</span>CHILLS</a>
        <ul class="menu-items">
            <li><a href="index.php">HOME</a></li>
            <li><a href="category.php?category=latest">LATEST</a></li>
            <li><a href="category.php?category=news">NEWS</a></li>
            <li><a href="category.php?category=rumours">RUMOURS</a></li>
            <li><a href="category.php?category=opinion">OPINION</a></li>
            <li><a href="category.php?category=previews">PREVIEWS</a></li>
            <li><a href="category.php?category=reviews">REVIEWS</a></li>
            <?php if (!is_logged_in()): ?>
            <span class="login-prompt">
                <a href="login.php">Log in</a> / <a href="signup.php">Sign up</a>
            </span>
            <?php else: ?>
            <span class="login-prompt">
                <a href="logout.php">Log Out</a>
            </span>
            <?php endif; ?>
        </ul>
        <div class="hamburger">
            <div class="hamburger-lines" id="hamburger-line-1"></div>
            <div class="hamburger-lines" id="hamburger-line-2"></div>
            <div class="hamburger-lines" id="hamburger-line-3"></div>
        </div>
        <div class="slide-in-menu">
            <a href="index.php" class="logo">I<span class="logo-accent">GOT</span>CHILLS</a>
            <ul class="slide-in-menu-items">
                <li class="slide-in-menu-item"><a href="index.php">HOME</a></li>
                <li class="slide-in-menu-item"><a href="category.php?category=latest">LATEST</a></li>
                <li class="slide-in-menu-item"><a href="category.php?category=news">NEWS</a></li>
                <li class="slide-in-menu-item"><a href="category.php?category=rumours">RUMOURS</a></li>
                <li class="slide-in-menu-item"><a href="category.php?category=opinion">OPINION</a></li>
                <li class="slide-in-menu-item"><a href="category.php?category=previews">PREVIEWS</a></li>
                <li class="slide-in-menu-item"><a href="category.php?category=reviews">REVIEWS</a></li>
            </ul>
            <span class="login-prompt">
                <a href="login.php">Log in</a> / <a href="signup.php">Sign up</a>
            </span>
            <section class="footer-socials">
                <a href="" class="fab fa-facebook"></a>
                <a href="" class="fab fa-twitter"></a>
                <a href="" class="fab fa-youtube"></a>
                <a href="" class="fab fa-instagram"></a>
                <a href="" class="fab fa-tiktok"></a>
            </section>
        </div>
    </nav>
