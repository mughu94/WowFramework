<?php
    $e = $data["error"];
    $msg = sprintf('<h1>500 Internal Server Error</h1>' . '<h3>%s (%s)</h3>' . '<pre>%s</pre>', $e->getMessage(), $e->getCode(), $e->getTraceAsString());
    echo $msg;
?>