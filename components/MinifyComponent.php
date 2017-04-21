<?php
/**
 * Minify.php
 * @author Revin Roman
 * @link https://processfast.com
 */

namespace processfast\yii\minify\components;

use processfast\yii\minify\View;


/**
 * Class MinifyComponent
 * @package processfast\yii\minify\components
 */
abstract class MinifyComponent
{

    /**
     * @var View
     */
    protected $view;

    /**
     * MinifyComponent constructor.
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    abstract public function export();

    /**
     * @param string $file
     * @return string
     */
    protected function getAbsoluteFilePath($file)
    {
        return \Yii::getAlias($this->view->basePath) . str_replace(\Yii::getAlias($this->view->webPath), '', $this->cleanFileName($file));
    }

    /**
     * @param string $file
     * @return string
     */
    protected function cleanFileName($file)
    {
        return (strpos($file, '?'))
            ? parse_url($file, PHP_URL_PATH)
            : $file;
    }

    /**
     * @param string $file
     * @param string $html
     * @return bool
     */
    protected function thisFileNeedMinify($file, $html)
    {
        return !$this->isUrl($file, false)
        && !$this->isContainsConditionalComment($html)
        && !$this->isExcludedFile($file);
    }

    /**
     * @param string $url
     * @param boolean $checkSlash
     * @return bool
     */
    protected function isUrl($url, $checkSlash = true)
    {
        $schemas = array_map(function ($val) {
            return str_replace('/', '\/', $val);
        }, $this->view->schemas);

        $regexp = '#^(' . implode('|', $schemas) . ')#is';
        if ($checkSlash) {
            $regexp = '#^(/|\\\\|' . implode('|', $schemas) . ')#is';
        }

        return (bool)preg_match($regexp, $url);
    }

    /**
     * @param string $string
     * @return bool
     */
    protected function isContainsConditionalComment($string)
    {
        return strpos($string, '<![endif]-->') !== false;
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function isExcludedFile($file)
    {
        $result = false;

        if (!empty($this->view->excludeFiles)) {
            foreach ((array)$this->view->excludeFiles as $excludedFile) {
                $reg = sprintf('!%s!i', $excludedFile);

                if (preg_match($reg, $file)) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param string $resultFile
     * @return string
     */
    protected function prepareResultFile($resultFile)
    {
        if( !$this->view->S3Upload )
        {
            $file = sprintf('%s%s', \Yii::getAlias($this->view->webPath), str_replace(\Yii::getAlias($this->view->basePath), '', $resultFile));
        }
        else
        {
            $file = $resultFile ;
        }

        $AssetManager = $this->view->getAssetManager();

        if ($AssetManager->appendTimestamp && ($timestamp = @filemtime($resultFile)) > 0) {
            $file .= "?v=$timestamp";
        }

        return $file;
    }

    /**
     * @param array $files
     * @return string
     */
    protected function _getSummaryFilesHash($files)
    {
        $result = '';
        foreach ($files as $file => $html) {
            $path = $this->getAbsoluteFilePath($file);


            if( $this->view->modifyPath )
            {
                $modifyPathData = $this->view->modifyPathData ;
                if( strpos( $path , $modifyPathData ) !== false )
                {
                    $path = str_replace( $modifyPathData , '' , $path );
                }
            }

            if ($this->thisFileNeedMinify($file, $html) && file_exists($path)) {
                switch ($this->view->fileCheckAlgorithm) {
                    default:
                    case 'filemtime':
                        $result .= filemtime($path) . $file;
                        break;
                    case 'sha1':
                        $result .= sha1_file($path);
                        break;
                }
            }
        }

        return sha1($result);
    }

    protected function uploadToS3( $resultFile , $type )
    {
        $versionName = self::getVersionName() ;

        $env = "" ;
        if( YII_ENV == 'dev' )
        {
            $env = "dev" ;
        }
        else if( YII_ENV == 'qa'  )
        {
            $env = "qa" ;
        }
        else if( YII_ENV == 'prod'  )
        {
            $env = "prod" ;
        }

        $mime_type = null ;
        $fileName =  null ;
        if( $type == "CSS" )
        {
            $prefix = "" ;
            $layout = \Yii::$app->controller->layout ;
            if( in_array( $layout , ["old_main","public_pages"] ) )
            {
                $prefix = "public-pages-" ;
            }

            $mime_type = "text/css" ;
            $fileName = "web-assets/".$env."/minify/".$prefix."all-in-one.$versionName.css";
        }
        else if( $type == "JS" )
        {
            $mime_type = "application/javascript" ;
            $fileName = "web-assets/".$env."/minify/all-in-one.$versionName.js";
        }

        $file = $this->handleAssetUpload( $resultFile , $mime_type , $fileName );

        return $file;
    }

    protected function getS3Path( $resultFile , $type )
    {
        $versionName = self::getVersionName() ;

        $env = "" ;
        if( YII_ENV == 'dev' )
        {
            $env = "dev" ;
        }
        else if( YII_ENV == 'qa'  )
        {
            $env = "qa" ;
        }
        else if( YII_ENV == 'prod'  )
        {
            $env = "prod" ;
        }

        $mime_type = null ;
        $fileName =  null ;
        if( $type == "CSS" )
        {
            $prefix = "" ;
            $layout = \Yii::$app->controller->layout ;
            if( in_array( $layout , ["old_main","public_pages"] ) )
            {
                $prefix = "public-pages-" ;
            }

            $mime_type = "text/css" ;
            $fileName = "web-assets/".$env."/minify/".$prefix."all-in-one.$versionName.css";
        }
        else if( $type == "JS" )
        {
            $mime_type = "application/javascript" ;
            $fileName = "web-assets/".$env."/minify/all-in-one.$versionName.js";
        }

        $file = $this->checkS3Path( $fileName , $resultFile , $mime_type );

        return $file;
    }

    protected function handleAssetUpload($temp, $type, $fileName, $acl = "public-read", $storageClass = "STANDARD")
    {
        $aws = \Yii::$app->awssdk->getAwsSdk();
        $s3 = $aws->createS3();
        $bucket = $this->view->awsBucket;

        try
        {
            $result = $s3->putObject(array(
                'Bucket' => $bucket,
                'Key' => $fileName,
                'SourceFile' => $temp,
                'ContentType' => $type,
                'ACL' => $acl,
                'StorageClass' => $storageClass
            ));
        }
        catch (Exception $e)
        {
            throw new HttpException(431, 'Image upload failed to bucket.');
        }

        return $this->s3Path( $bucket , $fileName );
    }

    protected function getVersionName()
    {
        $sql = 'SELECT current_version FROM `application_version`' ;
        $version = \Yii::$app->db->createCommand( $sql )->queryScalar();
        return $version;
    }

    protected function s3Path( $bucket , $fileName )
    {
        return "https://s3.amazonaws.com/".$bucket."/".$fileName;
    }

    protected function checkS3Path( $fileName , $resultFile , $type  )
    {
        $aws = \Yii::$app->awssdk->getAwsSdk();
        $s3 = $aws->createS3();
        $bucket = $this->view->awsBucket;

        if( $s3->doesObjectExist( $bucket , $fileName ) )
        {
            return $this->s3Path( $bucket , $fileName );
        }
        else
        {
            return $this->handleAssetUpload( $resultFile , $type ,$fileName );
        }
    }

}
