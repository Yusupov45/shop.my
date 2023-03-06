<?
if (!defined('VG_ACCESS')) {
    die('Access denied');
}

const TEMPLATE = 'templates/default/';
const ADMIN_TEMPLATE = 'core/admin/view/';
const UPLOAD_DIR = 'userfiles/';

const COOKIE_VERSION = '1.0.0';
const CRYPT_KEY = '';
const COOKIE_TIME = 60;
const BLOCK_TIME = 3;

const QTY = 8;
const QTY_LINKS = 3;

const ADMIN_CSS_JS = [
    'styles' => ['css/main.css'],
    'scripts' => []
];

const USER_CSS_JS = [
    'styles' => [],
    'script' => []
];

use core\base\exceptions\RouteException;

function autoloadMainClasses($className) {
    $className = str_replace('\\', '/', $className);

    if(!include_once $className.'.php') {
        throw new RouteException('Неверное имя файла для подключения - '.$className);
    }
}

spl_autoload_register('autoloadMainClasses');
?>