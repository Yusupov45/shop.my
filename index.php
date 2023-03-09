<?
define('VG_ACCESS', true);

header('Content-Type:text/html;charset=utf-8');
session_start();

require_once 'config.php';
require_once 'core/base/settings/internal_settings.php';

use core\base\exceptions\RouteException;
use core\base\exceptions\DbException;
use core\base\controller\RouteController;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
    RouteController::instance()->route();
}
catch (RouteException $e) {
    die($e->getMessage());
}
catch (DbException $e) {
    die($e->getMessage());
}
?>