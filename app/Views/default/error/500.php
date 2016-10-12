<?php
    /**
     * @var \Wow\Template\View $this
     * @var array              $model
     * @var Exception          $e
     */
    $this->set("title", $this->translate("error/500/title"));
    $this->response->status(500);
    $e = $model["error"];
?>
<div class="container">
    <h1><?php echo $this->translate("error/500/title"); ?></h1>
    <p><?php echo $this->translate("error/500/description"); ?></p>
    <p>
        <a class="btn btn-xs btn-default" href="javascript:void(0);" onclick="document.getElementById('divErrorDetails').style.display='block';"><?php echo $this->translate("error/500/see_details"); ?></a>
    </p>
    <div style="display: none;" id="divErrorDetails">
        <?php if($e instanceof Exception) { ?>
            <h3><?php echo $e->getMessage(); ?> (<?php echo $e->getCode(); ?>)</h3>
            <pre><?php echo $e->getTraceAsString(); ?></pre>
        <?php } else { ?>
            <p><?php echo $this->translate("error/500/no_details"); ?></p>
        <?php } ?>
    </div>
</div>
