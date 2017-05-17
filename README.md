Yii 2 Minify View Component
===========================

The main feature of this component - concatenate and compress files 
connected through "AssetBundle". And added new functionality to upload them to AWS S3 bucket directly from minify folder on file generation. And it also adds functionality to generate assets and upload to S3 from console controller.But to have functionality of generating assests from console  [ Script runs it on deployment after composer install finishes. ] and uploading S3 you must have to follow rules of defining all the assets used in web application must be loaded everytime. You can differenttiate it with layouts but for every page in layout there should be same number of JS/CSS files. All the js/css files belongs to widgets must be also registered on page load instead of widget initialization. They must follow dependency structure in a way that all JS/CSS files generated follow same sequence in all the pages in same layout. Below in description i have added how i have given dependecies to my all asset bundles.



[![License](https://poser.pugx.org/ProcessFast/yii2-minify-view/license.svg)](https://packagist.org/packages/ProcessFast/yii2-minify-view)
[![Latest Stable Version](https://poser.pugx.org/ProcessFast/yii2-minify-view/v/stable.svg)](https://packagist.org/packages/ProcessFast/yii2-minify-view)
[![Latest Unstable Version](https://poser.pugx.org/ProcessFast/yii2-minify-view/v/unstable.svg)](https://packagist.org/packages/ProcessFast/yii2-minify-view)
[![Total Downloads](https://poser.pugx.org/ProcessFast/yii2-minify-view/downloads.svg)](https://packagist.org/packages/ProcessFast/yii2-minify-view)

Code Status
-----------
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ProcessFast/yii2-minify-view/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ProcessFast/yii2-minify-view/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/ProcessFast/yii2-minify-view/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ProcessFast/yii2-minify-view/?branch=master)
[![Travis CI Build Status](https://travis-ci.org/ProcessFast/yii2-minify-view.svg)](https://travis-ci.org/ProcessFast/yii2-minify-view)
[![Dependency Status](https://www.versioneye.com/user/projects/54119b4b9e1622a6510000e1/badge.svg)](https://www.versioneye.com/user/projects/54119b4b9e1622a6510000e1)

Support
-------
[GutHub issues](https://github.com/ProcessFast/yii2-minify-view/issues).

Installation
------------

The preferred way to install this extension is through [composer](https://getcomposer.org/).

Either run

```bash
composer require "processfast/yii2-minify-view:~1.14"
```

or add

```
"processfast/yii2-minify-view": "~1.14",
```

to the `require` section of your `composer.json` file.

Configure
---------
```php
<?php

return [
	// ...
	'components' => [
		// ...
		'view' => [
			'class' => '\processfast\yii\minify\View',
			'enableMinify' => !YII_DEBUG,
			'concatCss' => true, // concatenate css
			'minifyCss' => true, // minificate css
			'concatJs' => true, // concatenate js
			'minifyJs' => true, // minificate js
			'minifyOutput' => true, // minificate result html page
			'webPath' => '@web', // path alias to web base
			'basePath' => '@webroot', // path alias to web base
			'minifyPath' => '@webroot/minify', // path alias to save minify result
			'jsPosition' => [ \yii\web\View::POS_END ], // positions of js files to be minified
			'forceCharset' => 'UTF-8', // charset forcibly assign, otherwise will use all of the files found charset
			'expandImports' => true, // whether to change @import on content
			'compressOptions' => ['extra' => true], // options for compress
			'excludeFiles' => [
            	'jquery.js', // exclude this file from minification
            	'app-[^.].js', // you may use regexp
    ],
    'excludeBundles' => [
            	\dev\helloworld\AssetBundle::class, // exclude this bundle from minification
    ],
            
    // Extra options added in this extention fork
   
   'S3Upload'=> true,         
   'awsBucket'=> null,
   'assetsFolderPathPatch'=>null,
   
   'backendCheck'=>false,
   'folderName'=>'minify',

   // This 2 options only used when you are generating from Script before deployment from Console Controller
   'modifyPath'=>false,
   'modifyPathData'=>"",


   'layoutPrefixArray'=>[],
   'layoutPrefixCss'=>false,
   'layoutPrefixJS'=>false
            
            
		]
	]
];
```

New Configuration Options
-------------------------

```
 /**
     * @var boolean
     * whether you want to use S3Bucket or not
     * By default it will be false
     */
    public $S3Upload = false ;

    /**
     * @var boolean
     * Name of awsBucket
     */
    public $awsBucket = null ;

    /**
     * @var boolean
     * It is for linking Resource folder to asset files
     * if Resources like images above one folder it should be "../" if two folders above "../../"
     * You want to load all the images from s3 now you have images folder in root and you have dev , qa , prod folder
     * and css and js inside those folder now you have to link images in those css file.
     * You have to use this option to do that.
     */
    public $assetsFolderPathPatch = null ;

    /*
     * boolean
     * backend checke will help take asset from root/minify folder for backedn instead of root/backend/minifiy
     * If backend and frontend has same assets and you want to use same location to store asset
     * you can make thi true. By this it will use root/minify other then root/backedn/minify
     * to copy files to S3
     */
    public $backendCheck = false ;

    /*
     * Folder name where minified files will be kept
     * Here i have devided it will be used when $backendCheck is true
     * as if we are doing from web from backend / frontend to have same file hashes
     * as I am using same assets folder in root for backend / frontend as to have files from same assets folder
     */
    public $folderName = 'minify' ;

    /*
     * will be used at _getSummaryFilesHash will fix path to have same hash value as frontend or backend when files generated from console.
     * At console level this will be used as when generating from console path will be different so some adjustment path should be decalared t make path same as
     * of running in web browser as console has path from console folder script
     *
     * so when you generate from console  make modifyPath true
     * and modifyPathData regarding your assets folder to console folder
     */
    public $modifyPath = false  ;
    public $modifyPathData = "" ;

    /*
     * It helps if you want to add prefix to any file as it will mostly create file name
     * as {prefix}-all-in-one-{HASH}.{js/css}
     * so if you want to give a prefix for certain layout
     * then you can do it by this option.
     * Pass layout name as array key and pass prefix name as array value
     * ex :  for main layout if you want newmain prefix
     * you have to pass array like ["main"=>"newmain"]
     * if you do not wont prefix do not do anything just live it a blank array
     */
    public $layoutPrefixArray = [] ;

    /*
     * Use layoutPrefixArray option for css true/false
     */
    public $layoutPrefixCss = false ;

    /*
     * Use layoutPrefixArray option for Js true/false
     */
    public $layoutPrefixJS = false ;
```



My Asset bundles dependencies
-----------------------------

By giving dependencies and loading all JS/CSS files throught web application in a uniform sequence will create uniform minified JS/CSS files from extention for all pages as all pages have same JS/CSS files with same sequence. As the final name of JS/CSS file depends on the file content and file path so it is important to have configuration in this way.

This is just example of my AssetBundle dependency you can create yours on the basis of your asset bundles.

```
<?php

if( YII_DEBUG )
{
    $jquery = "https://code.jquery.com/jquery-2.2.4.js" ;
}
else
{
    $jquery = "https://code.jquery.com/jquery-2.2.4.min.js" ;
}

return [

    'yii\web\JqueryAsset' => [
        'js'=>[$jquery]
    ],
    'common\assets\HighchartsAsset' => [
        'depends'=>[
            'yii\web\JqueryAsset'
        ],
    ],
    'yii\web\YiiAsset' => [
        'depends'=>[
            'common\assets\HighchartsAsset'
        ],
    ],
    'yii\validators\ValidationAsset' => [
        'depends'=>[
            'yii\web\YiiAsset'
        ],
    ],
    'yii\widgets\ActiveFormAsset' => [
        'depends'=>[
            'yii\validators\ValidationAsset'
        ],
    ],
    'yii\bootstrap\BootstrapAsset' => [
        'css' => [], // do not use yii default one,
        'depends'=>[
            'yii\widgets\ActiveFormAsset'
        ],
    ],
    'yii\widgets\MaskedInputAsset' => [
        'depends'=>[
            'yii\bootstrap\BootstrapAsset'
        ],
    ],
    'yii\jui\JuiAsset' => [
        'depends'=>[
            'yii\widgets\MaskedInputAsset'
        ],
    ],
    'common\assets\MomentJsAsset' => [
        'depends'=>[
            'yii\jui\JuiAsset'
        ],
    ],
    'common\assets\CDNAsset' => [
        'depends'=>[
            'common\assets\MomentJsAsset'
        ],
    ],
    'mihaildev\ckeditor\Assets' => [
        'depends'=>[
            'common\assets\CDNAsset'
        ],
    ],
    'yii\bootstrap\BootstrapPluginAsset' => [
        'depends'=>[
            'mihaildev\ckeditor\Assets'
        ],
    ],
    'kartik\form\ActiveFormAsset' => [
        'depends'=>[
            'yii\bootstrap\BootstrapPluginAsset'
        ],
    ],
    'kartik\time\TimePickerAsset' => [
        'depends'=>[
            'kartik\form\ActiveFormAsset'
        ],
    ],

    'kartik\file\SortableAsset' => [
        'depends'=>[
            'kartik\time\TimePickerAsset'
        ],
    ],
    'kartik\file\DomPurifyAsset' => [
        'depends'=>[
            'kartik\file\SortableAsset'
        ],
    ],

    'kartik\file\FileInputAsset' => [
        'depends'=>[
            'kartik\file\DomPurifyAsset'
        ],
    ],
    'kartik\dropdown\DropdownXAsset' => [
        'depends'=>[
            'kartik\file\FileInputAsset'
        ],
    ],
    'kartik\base\WidgetAsset' => [
        'depends'=>[
            'kartik\dropdown\DropdownXAsset'
        ],
    ],
    'common\assets\FontAwesomeAsset' => [
        'depends'=>[
            'kartik\base\WidgetAsset'
        ],
    ],
    'common\assets\IonIconsAsset' => [
        'depends'=>[
            'common\assets\FontAwesomeAsset'
        ],
    ],
    'common\assets\JqueryCreditCardValidatorAsset' => [
        'depends'=>[
            'common\assets\IonIconsAsset'
        ],
    ],
    'common\assets\ListJsAsset' => [
        'depends'=>[
            'common\assets\JqueryCreditCardValidatorAsset'
        ],
    ],
    'common\assets\MustacheJsAsset' => [
        'depends'=>[
            'common\assets\ListJsAsset'
        ],
    ],
    'common\assets\JsCookieAsset' => [
        'depends'=>[
            'common\assets\MustacheJsAsset'
        ],
    ],
    'common\assets\BootstrapDaterangePickerAsset' => [
        'depends'=>[
            'common\assets\JsCookieAsset'
        ],
    ],
    'common\assets\BootstrapDateTimePickerAsset' => [
        'depends'=>[
            'common\assets\BootstrapDaterangePickerAsset'
        ],
    ],
    'common\assets\BootstrapSwitchAsset' => [
        'depends'=>[
            'common\assets\BootstrapDateTimePickerAsset'
        ],
    ],
    'common\assets\AdminLTEAsset' => [
        'depends'=>[
            'common\assets\BootstrapSwitchAsset'
        ],
    ],
    'common\assets\AppAssetVersion2' => [
        'depends'=>[
            'common\assets\AdminLTEAsset'
        ],
    ],
    'common\assets\AppAsset' => [
        'depends'=>[
            'common\assets\AppAssetVersion2'
        ],
    ],
] ; 

```

This is some fix i have to do it at my side to make things work.


```
        // start of config/main.php
        // removing js and css files being loaded on AJAX call
        $bundlesFiles = require_once( $bundles ) ;
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $bundlesFiles = false ;
        }


        // Under components of config/main.php

        'assetManager' => [
            /*
             * when u have to make fix for images you will need it
            */
            'hashCallback'=>function( $path ){

                $path1 = $path ;
                $path = \common\components\Helper::getPathIdentical( $path );
                $hash = hash( 'md4' , $path );
                return $hash;
                
            },

            'bundles' => $bundlesFiles,
        ],


    public static function getPathIdentical( $path )
    {
        if( strpos( $path , "_protected" ) !== false )
        {
            return self::sliceIt( $path ,  "_protected");
        }
        else if( strpos( $path , "themes" ) !== false )
        {
            return self::sliceIt( $path ,  "themes");
        }
        else
        {
            if( YII_DEBUG && strpos( $path , "minify-final" ) === false)
            {
                echo "Let Jaimin MosLake Know this happen. We made asset bundle with new directory. Add dependecny.";
                echo $path;
                exit;
            }
        }

        return $path ;
    }

    public static function sliceIt( $path , $sliceBy )
    {
        $explodeArray = explode( $sliceBy , $path );
        $backString = null;
        if( sizeof($explodeArray) >= 2 )
        {
            $backString = $sliceBy.$explodeArray[1];
        }

        return $backString;
    }

     

```


My AssetMinifyController ( Console Controller )
-----------------------------------------------

This will be used if you have scripts to deploy your all code from github push to your server and you have/do not have load balancing environment but if you want to generate all the assets from Script then you can call console  action in your script and initialize generation of all the asset bundles on your servers before any real call. This way on load balanced environment without sticky sessions all your server will have assets folder as well as It will generate minified files and upload it to S3.


```
<?php

namespace console\controllers;


define('RUNNING_FROM_CONSOLE', true );


use common\assets\AppAssetVersion2;
use common\assets\HighchartsAsset;
use common\components\AppBasic;
use yii\console\Controller;
use processfast\yii\minify\View;
use common\assets\AppAsset;
use processfast\yii\minify\components\CSS;
use processfast\yii\minify\components\JS;
use Yii;
use yii\web\AssetBundle;

$url = "/dev/ops-insights/" ;
\Yii::setAlias('@webroot', \Yii::$app->basePath."/../../" );
\Yii::setAlias('@web', $url );


class AssetMinifyController extends Controller
{
    public function actionInit()
    {
        ini_set( 'max_execution_time' , 480 );

        $url = "/dev/ops-insights/" ;
        $webroot = \Yii::$app->basePath."/../../" ;
        $web = $url ;

        $view = new View();
        $view->S3Upload = true ;
        $view->awsBucket = 'opsinsights-storage' ;
        $view->assetsFolderPathPatch = '../../' ;
        $view->enableMinify = true ;
        $view->concatCss = true ; // concatenate css
        $view->minifyCss = true ; // minificate css
        $view->concatJs = true ; // concatenate js
        $view->minifyJs = true ; // minificate js
        $view->minifyOutput = true ; // minificate result html page
        $view->webPath = $web ;
        $view->basePath = $webroot ; // path alias to web base
        $view->minifyPath = $webroot.'/minify' ; // path alias to save minify result
        $view->jsPosition = [ \yii\web\View::POS_END ] ; // positions of js files to be minified
        $view->forceCharset = 'UTF-8' ; // charset forcibly assign, otherwise will use all of the files found charset
        $view->expandImports = true ; // whether to change @import on content
        $view->compressOptions = ['extra' => true]; // options for compress
        $view->excludeFiles = ['jquery.js', // exclude this file from minification
                                    'app-[^.].js', // you may use regexp
                                  ];
        $view->excludeBundles = [];
        $view->modifyPath = true ;
        $view->modifyPathData = '_protected/console/../../' ;

        $bundlesFiles = Yii::$app->params['bundles_minify'] ;
        $view->assetManager->bundles = $bundlesFiles ;


        $this->layout = "public_pages" ;
        $view->registerAssetBundle( AppAsset::className() );

        $assetBundle = $view->assetBundles ;
        // Revering it as it register asset bundle in reverse order. This array has reverse dependency
        // reversing array to give reverse dependency
        $assetBundle = array_reverse( $assetBundle );

        $this->assetBundleRegistration( $view ,  $assetBundle );
        (new CSS($view))->export();
        (new JS($view))->export();


        $this->layout = "main" ;
        $view->assetBundles = [] ;
        $view->cssFiles = [] ;
        $view->jsFiles = [] ;

        $view->registerAssetBundle( AppAssetVersion2::className() );

        $assetBundle = $view->assetBundles ;
        // Revering it as it register asset bundle in reverse order. This array has reverse dependency
        // reversing array to give reverse dependency
        $assetBundle = array_reverse( $assetBundle );

        $this->assetBundleRegistration( $view ,  $assetBundle );
        (new CSS($view))->export();
        (new JS($view))->export();

    }

    public function assetBundleRegistration( $view , $assetBundle)
    {
        foreach (array_keys( $assetBundle ) as $name) {

            if (!isset($view->assetBundles[$name])) {
                return;
            }
            $bundle = $view->assetBundles[$name];
            if ($bundle) {
                foreach ($bundle->depends as $dep) {
                    $this->assetBundleRegistration( $view , [$dep] );
                }
                $bundle->registerAssetFiles($view);
            }
            unset($view->assetBundles[$name]);
        }
    }

    public function registerBundle($view, $bundles, $name, &$registered)
    {
        if (!isset($registered[$name])) {
            $registered[$name] = false;
            $bundle = $bundles[$name];
            foreach ($bundle->depends as $depend) {
                $this->registerBundle($view, $bundles, $depend, $registered);
            }
            unset($registered[$name]);
            $registered[$name] = $bundle;
        } elseif ($registered[$name] === false) {
            throw new Exception("A circular dependency is detected for target.");
        }
    }


}

```

