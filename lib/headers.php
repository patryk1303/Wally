<?php

/**
 * changes request header for JSON desponse
 * @deprecated since version 0.1
 */
function JSONheader() {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
}

