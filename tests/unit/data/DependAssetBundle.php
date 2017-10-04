<?php
/**
 * DependAssetBundle.php
 * @author Revin Roman
 * @link https://processfast.ru
 */

namespace processfast\yii\minify\tests\unit\data;

/**
 * Class DependAssetBundle
 * @package processfast\yii\minify\tests\unit\data
 */
class DependAssetBundle extends \yii\web\AssetBundle
{

    public $js = [
        'depend.js',
    ];

    public $css = [
        'depend.css',
    ];

    public $jsOptions = [
        'position' => \processfast\yii\minify\View::POS_HEAD,
    ];

    public $depends = [
        'processfast\yii\minify\tests\unit\data\JQueryAssetBundle',
    ];

    public function init()
    {
        $this->sourcePath = __DIR__ . '/source';
    }
}
