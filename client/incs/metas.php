<?php $page_title = "Hope Phillip Charity Foundation"; ?>
<?php $page_description = "Hope Phillip Charity Foundation is dedicated to supporting vulnerable individuals through various initiatives focused on education, health, and livelihood."; ?>
<?php $page_keywords = "Charity, Non-Profit, Volunteer, Donate, Education, Health, Livelihood, Hope Phillip Charity Foundation"; ?>
<?php // $base_url = ($_SERVER['HTTP_HOST'] === 'localhost') ? 'http://localhost/codes/hopephillipscharityfoundation/' : "http://hopephillipscharityfoundation.com/"; ?>
<?php 
$is_local = $_SERVER['HTTP_HOST'] === 'localhost';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
    ? "https://" 
    : "http://";

$base_url = $is_local 
    ? $protocol . "localhost/codes/hopephillipscharityfoundation/"
    : $protocol . "hopephillipscharityfoundation.com/";
?>

<title><?php echo $page_title; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    <link rel="canonical" href="<?php echo $base_url; ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo $base_url; ?>images/HPCF.main.png">
    <link href="https://fonts.googleapis.com/css?family=Mansalva|Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>fonts/icomoon/style.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/animate.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/jquery.fancybox.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/owl.carousel.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/owl.theme.default.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>fonts/flaticon/font/flaticon.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/aos.css">

    <!-- MAIN CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/style.css">