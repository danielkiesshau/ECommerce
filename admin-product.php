<?php
use \Hcode\PageAdmin;
use \Hcode\Model\Products;
use \Hcode\Model\User;



$app->get("/admin/products", function(){
    User::verifyLogin();
    $page = new PageAdmin();
    
    $products = Products::listAll();
    
    $page-setTpl("products",[
        'products'=>$products 
    ]);
});
?>