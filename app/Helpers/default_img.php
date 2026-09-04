<?php

function default_img($img)
{
    if($img) {
        return $img;
    } else {
        return '/assets/img/default.jpg';
    }
}