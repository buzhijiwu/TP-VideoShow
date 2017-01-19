<?php
function WD($name) {
    $class = '\\Common\\Model\\'.$name.'Model';
    $model = new $class($name);
    return $model;
}