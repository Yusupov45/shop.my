<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController {

    protected function inputData() {
        $db = Model::instance();

        $table = 'articles';

        $files['gallery_img'] = ["red''.jpg", 'blue.jpg', 'black'];
        $files['img'] = "main_img.jpg";

        $res = $db->add($table, [
            'fields' => ['name' => 'Olga', 'content' => 'hello', 'price' => 1.5],
            'except' => ['name'],
            'files' => $files
        ]);

        exit('admin');
    }

}