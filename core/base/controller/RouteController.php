<?
namespace core\base\controller;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;

class RouteController extends BaseController{  // разбирает адресную строку
    
    use Singleton;

    protected $routes;


    private function __construct()
    {
        $addressStr = $_SERVER['REQUEST_URI'];

        if($_SERVER['QUERY_STRING']) {
            $addressStr = substr($addressStr, 0, strpos($addressStr, $_SERVER['QUERY_STRING']) - 1);
        }

        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php'));

        if ($path === PATH) {

            if(strrpos($addressStr, '/') === strlen($addressStr) - 1 && strrpos($addressStr, '/') !== strlen(PATH) - 1) {
                $this->redirect(rtrim($addressStr, '/'), 301);
            } 

            $this->routes = Settings::get('routes');

            if (!$this->routes) {
                throw new RouteException('Отсутствуют маршруты в базовых настройках', 1);
            }

            $url = explode('/', substr($addressStr, strlen(PATH))); // обрезаем первый / который идет по умолчанию и создаем массив

            if ($url[0] && $url[0] === $this->routes['admin']['alias']) {

                array_shift($url);

                if ($url[0] && is_dir($_SERVER['DOCUMENT_ROOT'] . PATH . $this->routes['plugins']['path'] . $url[0])){ // проверка обращаемся ли к плагину
                    $plugin = array_shift($url);

                    $pluginSettings = $this->routes['settings']['path'] . ucfirst($plugin . 'settings'); //путь до класса настроек

                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . '.php')) {
                        $pluginSettings = str_replace('/', '\\', $pluginSettings); //получаем имя класса настроек плагина с неймспейсом
                        $this->routes = $pluginSettings::get('routes');
                    }

                    $dir = $this->routes['plugins']['dir'] ? '/' . $this->routes['plugins']['dir'] . '/' : '/'; //определяем если ли отдельная директория у плагина
                    $dir = str_replace('//', '/', $dir); // защита от двойных /

                    $this->controller = $this->routes['plugins']['path'] . $plugin . $dir; // путь до контроллеров плагина вроде как

                    $hrUrl = $this->routes['plugins']['hrUrl'];

                    $route = 'plugins';
                } 
                else {
                    $this->controller = $this->routes['admin']['path'];

                    $hrUrl = $this->routes['admin']['hrUrl'];

                    $route = 'admin';
                }

            }
            else {  

                $hrUrl = $this->routes['user']['hrUrl'];

                $this->controller = $this->routes['user']['path'];

                $route = 'user';
            }

            $this->createRoute($route, $url);

            if($url[1]) {
                $count = count($url);
                $key = '';

                if(!$hrUrl) {
                    $i = 1;
                }
                else {
                    $this->parameters['alias'] = $url[1];
                    $i = 2;
                }

                for(; $i < $count; $i++) {
                    if(!$key) {
                        $key = $url[$i];
                        $this->parameters[$key] = '';
                    }
                    else {
                        $this->parameters[$key] = $url[$i];
                        $key = '';
                    }
                }
            }

        } 
        else {
            throw new RouteException('Не корректная директория сайта', 1);
        }
    }


    private function createRoute($var, $arr) {
        $route = [];

        if(!empty($arr[0])) {
            if($this->routes[$var]['routes'][$arr[0]]) {
                $route = explode('/', $this->routes[$var]['routes'][$arr[0]]);

                $this->controller .= ucfirst($route[0].'Controller');
            }
            else {
                $this->controller .= ucfirst($arr[0].'Controller');
            }
        } 
        else {
            $this->controller .= $this->routes['default']['controller'];
        }

        $this->inputMethod = $route[1] ? $route[1] : $this->routes['default']['inputMethod'];
        $this->outputMethod = $route[2] ? $route[2] : $this->routes['default']['outputMethod'];

        return;
    } 
}
?>