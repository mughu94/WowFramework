<?php
    /**
     * @var \Wow\Template\View $this
     */
    $this->set("title", $this->translate("error/404/title"));
    $this->response->status(404);
?>
<div class="container">
    <h1><?php echo $this->translate("error/404/title"); ?></h1>
    <p><?php echo $this->translate("error/404/description"); ?></p>
    <p><a href="/" class="btn btn-default"><?php echo $this->translate("error/404/go_home"); ?></a></p>
</div>