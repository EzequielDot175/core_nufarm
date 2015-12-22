<?php

/**
 * Created by PhpStorm.
 * User: dot175
 * Date: 06/10/2015
 * Time: 04:12 PM
 */
class UserFiles
{
    public static $dir = null;

    public function __construct(){}



    public static function listFiles($json = false){
        if (is_dir(self::$dir)) {
           $dir = scandir(self::$dir);
            unset($dir[0]);
            unset($dir[1]);

            if ($json) {
                echo json_encode($dir);
            }else{
                return $dir;
            }
        }
    }

    public static function deleteFile($filename){
        if (is_dir(self::$dir)) {
            if (is_file(self::$dir."/".$filename)) {
                return unlink(self::$dir."/".$filename);
            }
        }
    }
}