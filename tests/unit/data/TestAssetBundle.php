<?php
/**
 * TestAssetBundle.php
 * @author Revin Roman
 * @link https://processfast.ru
 */

namespace processfast\yii\minify\tests\unit\data;

/**
 * Class TestAssetBundle
 * @package processfast\yii\minify\tests\unit\data
 */
class TestAssetBundle extends \yii\web\AssetBundle
{

    public $js = [
        'test.js',
    ];

    public $css = [
        'test.css',
    ];

    public $jsOptions = [
        'position' => \processfast\yii\minify\View::POS_READY,
    ];

    public $depends = [
        'processfast\yii\minify\tests\unit\data\DependAssetBundle',
    ];

    public function init()
    {
        $this->sourcePath = __DIR__ . '/source';

        parent::init();
    }
}
