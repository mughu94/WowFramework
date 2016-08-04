<?php
    /**
     * Wow Master Template
     *
     * @var \Wow\Template\View $this
     */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/bootstrap/3.3.6/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="/assets/Style/Style.css">
    <title><?php if($this->has('title')) {
            echo $this->get("title") . " | ";
        } ?>Wow</title>
    <?php if($this->has('description')) { ?>
    <meta name="description" content="<?php echo $this->get("description"); ?>"><?php } ?>
    <?php if($this->has('keywords')) { ?>
    <meta name="keywords" content="<?php echo $this->get("keywords"); ?>"><?php } ?>
</head>
<body>
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/"><img src="/assets/Images/Logo.png"/></a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <p class="navbar-text navbar-right">
              Wellcome.
            </p>
        </div>
    </div>
</nav>
<div class="container">
    <?php $this->renderSection('section_pageheader'); ?>
    <?php $this->renderBody(); ?>
</div>
<?php $this->section('section_scripts'); ?>
<script src="/assets/jquery/2.2.4/jquery.min.js"></script>
<script src="/assets/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<?php $this->show(); ?>
</body>
</html>