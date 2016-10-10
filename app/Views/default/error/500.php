<?php
    /**
     * @var \Wow\Template\View $this
     * @var array              $model
     * @var Exception          $e
     */
    $this->response->status(500);
    $e   = $model["error"];
    $msg = sprintf('<h1>500 Internal Server Error</h1>' . '<h3>%s (%s)</h3>' . '<pre>%s</pre>', $e->getMessage(), $e->getCode(), $e->getTraceAsString());
    echo $msg;
?>