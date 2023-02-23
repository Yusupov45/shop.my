<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController {

    protected function inputData() {
        $db = Model::instance();

        $table = 'articles';

        $files['gallery_img'] = [''];
        $files['img'] = "";

        $_POST['id'] = 3;
        $_POST['name'] = 'Oksana';

        $res = $db->edit($table);


        exit('admin');
    }

}