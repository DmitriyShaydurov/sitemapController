<?php
namespace shaydurov\opencart;
/**
 * Description: Simple trait aka dd() in Laravel
 * could be used for Russian language
 * Author: Dmitriy Shaydurov <dmitriy.shaydurov@gmail.com>
 * Author URI: http://smb-studio.com
 * Version: 1.0
 * Date: 30.04.2016
 **/

trait PrintDie
{
    protected $show = true;
    protected $head = '<html lang="ru">
                        <!doctype html>
                        <head>
                          <!-- Required meta tags -->
                          <meta charset="utf-8">
                          <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                        </head>';
    public function h() {
        if ($this->show) {
            echo $this->head;
        }
    }

    public function p_r($array) {
        if ($this->show) {
            echo'<pre>';
            print_r($array);
            echo'</pre>';
        }
    }

    public function p_d($array) {
        if ($this->show) {
            echo'<pre>';
            print_r($array);
            echo'</pre></br>';
            die('here');
        }
    }

    public function e($var) {
        if ($this->show) {
            echo $var;
        }
    }

    public function e_d($var) {
        if ($this->show) {
            echo $var . '</br>';
            die('here');
        }
    }

    public function e_b($var) {
        if ($this->show) {
            echo $var . '<br />';
        }
    }
}
