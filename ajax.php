<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 15/04/2018
 * Time: 21:25
 */

require_once ('autoload.php');
use mod_uetanalytics\uet_ajax;
require_login();

if(!isset($_POST['action'])){
    echo json_encode($_POST);
}
$action = $_POST['action'];
$params = $_POST;
$ajax = new uet_ajax();
echo $ajax->ajax($action,$params);