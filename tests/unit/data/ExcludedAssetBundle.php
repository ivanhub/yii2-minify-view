<?php
/**
 * ExcludedAssetBundle.php
 * @author Revin Roman
 * @link https://processfast.com
 */

namespace processfast\yii\minify\tests\unit\data;

/**
 * Class ExcludedAssetBundle
 * @package processfast\yii\minify\tests\unit\data
 */
class ExcludedAssetBundle extends \yii\web\AssetBundle
{

    public $css = [
        'excluded.css',
    ];

    public $js = [
        'excluded.js',
    ];

    public function init()
    {
        $this->sourcePath = __DIR__ . '/source';
    }
}
