<?php
/**
 * JS.php
 * @author Revin Roman
 * @link https://processfast.com
 */

namespace processfast\yii\minify\components;

use yii\helpers\Html;

/**
 * Class JS
 * @package processfast\yii\minify\components
 */
class JS extends MinifyComponent
{

    public function export()
    {
        $jsFiles = $this->view->jsFiles;

        $jsPosition = $this->view->jsPosition;
        $jsOptions = $this->view->jsOptions;

        if (!empty($jsFiles)) {
            foreach ($jsFiles as $position => $files) {
                if (false === in_array($position, $jsPosition, true)) {
                    $this->view->jsFiles[$position] = [];

                    foreach ($files as $file => $html) {
                        $this->view->jsFiles[$position][$file] = $html;
                    }
                } else {
                    $this->view->jsFiles[$position] = [];

                    $toMinify = [];

                    foreach ($files as $file => $html) {
                        if ($this->thisFileNeedMinify($file, $html)) {
                            if ($this->view->concatJs) {
                                $toMinify[$file] = $html;
                            } else {
                                $this->process($position, $jsOptions, [$file => $html]);
                            }
                        } else {
                            if (!empty($toMinify)) {
                                $this->process($position, $jsOptions, $toMinify);

                                $toMinify = [];
                            }

                            $this->view->jsFiles[$position][$file] = $html;
                        }
                    }

                    if (!empty($toMinify)) {
                        $this->process($position, $jsOptions, $toMinify);
                    }

                    unset($toMinify);
                }
            }
        }
    }

    /**
     * @param integer $position
     * @param array $options
     * @param array $files
     */
    protected function process($position, $options, $files)
    {
        $hash = $this->_getSummaryFilesHash($files) ;
        $resultFile = sprintf('%s/%s.js', $this->view->minifyPath, $hash);

        if(  $this->view->S3Upload && $this->doesObjectExist( $resultFile , "JS" , $hash ) )
        {
            // It exist on s3 so just get
            $resultFile = $this->getS3Path( $resultFile , "JS" , $hash );
        }
        else if (!file_exists($resultFile))
        {
            $js = '';

            foreach ($files as $file => $html) {
                $file = $this->getAbsoluteFilePath($file);

                $content = '';

                if (!file_exists($file)) {
                    \Yii::warning(sprintf('Asset file not found `%s`', $file), __METHOD__);
                } elseif (!is_readable($file)) {
                    \Yii::warning(sprintf('Asset file not readable `%s`', $file), __METHOD__);
                } else {
                    $content .= file_get_contents($file) . ';' . "\n";
                }

                $js .= $content;
            }

            $this->removeJsComments($js);

            if ($this->view->minifyJs) {
                $js = (new \JSMin($js))
                    ->min();
            }

            $js = gzencode( $js , 9 );
            file_put_contents($resultFile, $js);

            if (false !== $this->view->fileMode) {
                @chmod($resultFile, $this->view->fileMode);
            }

            if( $this->view->S3Upload )
            {
                $resultFile = $this->uploadToS3( $resultFile , "JS" , $hash);
            }
        }
        else
        {
            if( $this->view->S3Upload )
            {
                $resultFile = $this->uploadToS3( $resultFile , "JS" , $hash);
            }
        }

        $file = $this->prepareResultFile($resultFile);

        $this->view->jsFiles[$position][$file] = Html::jsFile($file, $options);
    }

    /**
     * @todo
     * @param string $code
     */
    protected function removeJsComments(&$code)
    {
        if (true === $this->view->removeComments) {
            //$code = preg_replace('', '', $code);
        }
    }
}
