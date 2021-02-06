<?php
/*
 * author : ph09nix
 * github : github.com/ph09nix
 * telegram : @ph09nix
 * gmail : ph09nixom@gmail.com
 */
require_once "msqlhelper.php";
{
    $helper = new msqlhelper("localhost", "root", "", "test", true);
    if ($helper->connect($error)) {
        // insert new row with knowing column names
        $helper->insert("users", [
            "cl1" => "value 1",
            "cl2" => "value 2",
        ]);
        // insert new row without knowing column names
        $helper->insert("users", [

            "value 1",
            "value 2",
        ]);


        // update a row with knowing column names
        $helper->update("users",
            [
                "column1" => "new value 1",
            ],
            [
                "column1" => "old value"
            ]);
        // update a row without knowing column names
        $helper->update("users",
            [
                "new value 1",
            ],
            [
                "old value"
            ]);

        // delete a row with knowing column names
        $helper->delete("users", [
            "cl1" => "value 1",
            "cl2" => "value 2",
        ]);
        // delete a row with knowing column names
        $helper->delete("users", [
            "value 1",
            "value 2",
        ]);

        // select a row
        $helper->select("users",[
           "column1"=>"value1",
           "column2 CONTAINS"=>"value2",
           "column3 LENGTH"=>3
        ]);
        // above command , will returns rows that `column1` equals to `value1`, and `column2`
        // contains `value2` text and `column3` length is `3`
    } else {
        echo $error;
    }

}
?>