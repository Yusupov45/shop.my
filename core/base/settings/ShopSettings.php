<?

namespace core\base\settings;

use core\base\controller\Singleton;

class ShopSettings {

    use Singleton;

    private $baseSettings;
    
    private $routes = [
        'plugins' => [
            'dir' => false,
            'routes' => [

            ]
        ]
    ];

    private $templateArr = [
        'text' => [
            'price',
            'short'
        ],
        'textarea' => [
            'goods_content'
        ]
    ];

    public static function get($property)
    {
        return self::getInstance()->$property;
    }

    public static function getInstance() 
    {
        if (self::$_instance instanceof self) {
            return self::$_instance;
        }

        self::instance()->baseSettings = Settings::instance();
        $baseProperties = self::$_instance->baseSettings->clueProperties(self::class); 
        self::$_instance->setProperty($baseProperties);

        return self::$_instance;
    }

    protected function setProperty($properties) 
    {
        if($properties) {
            foreach($properties as $name => $property) {
                $this->$name = $property;
            }
        }
    }

}