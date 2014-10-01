<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2014 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\console\controllers;

use yii\base\Exception;
use yii\console\Controller;


/**
 * Base class for application management controller
 * @package console\controllers
 * @author Tobias Munk <tobias@diemeisterei.de>
 */
class BaseAppController extends Controller
{
    public $composerExecutables = ['composer.phar', 'composer'];
    protected $_composerExecutable = null;

    public function init()
    {
        parent::init();
        foreach ($this->composerExecutables AS $cmd) {
            exec($cmd, $output, $return);
            if ($return == 0) {
                $this->_composerExecutable = $cmd;
                return;
            }
        }
        throw new Exception('Composer executable not found.');
    }

    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }


    protected function composer($command)
    {
        $this->execute($this->_composerExecutable . ' ' . $command);
    }

    protected function execute($command)
    {
        echo "\nExecuting '$command'...\n";
        if (($fp = popen($command, "r"))) {
            while (!feof($fp)) {
                echo fread($fp, 1024);
                flush(); // you have to flush buffer
            }
            fclose($fp);
        }
    }

    protected function action($command, $params = [])
    {
        echo "\n\nRunning action '$command'...\n";
        \Yii::$app->runAction($command, $params);
    }

    protected function readConfigurationValue($file, $id)
    {
        $marker  = "#value:{$id}";
        $subject = file_get_contents($file);
        $regex   = "/(\s*'[^']*'\s*=>\s*')([^']*)(',\s*" . $marker . ")/";
        preg_match($regex, $subject, $matches);
        if (!isset($matches[2])) {
            echo "Marker '{$marker}' not found in config file '{$file}'.\n";
            return null;
        } else {
            return $matches[2];
        }
    }

    protected function promptUpdateConfigurationValue($file, $id, $prompt)
    {
        $originalValue = $this->readConfigurationValue($file, $id);
        if ($originalValue !== null) {
            $value = $this->prompt($prompt, ['default' => $this->readConfigurationValue($file, $id)]);
            $this->updateConfigurationValue($file, $id, $value);
        }
    }

    protected function updateConfigurationValue($file, $id, $value)
    {
        $marker  = "#value:{$id}";
        $subject = file_get_contents($file);
        $regex   = "/(\s*'[^']*'\s*=>\s*')[^']*(',\s*" . $marker . ")/";
        $content = preg_replace($regex, "$1" . $value . "$2", $subject);
        file_put_contents($file, $content);
    }

    protected function addToConfigurationArray($file, $id, $item)
    {
        $marker      = "#array:{$id}>end#";
        $valueString = substr(\yii\helpers\VarDumper::export($item), 2, -2); // use value without enclosing brackets
        $subject     = file_get_contents($file);
        if (!$this->validateConfigurationChange($subject, $marker, $item)) {
            echo "Not updated configuration array '{$id}' in '{$file}'.\n";
            return false;
        } else {
            preg_match("/(\s*)('[^']*'\s*=>\s*'[^']*'),/", $subject, $matches);
            $replacement = $valueString . "\n" . $marker;
            $content     = str_replace($marker, $replacement, $subject);
            file_put_contents($file, $content);
            echo "Updated configuration array '{$id}' in '{$file}'.\n";
            return true;
        }
    }

    protected function validateConfigurationChange($configurationString, $marker, $item)
    {
        if (!strstr($configurationString, $marker)) {
            // marker does not exist
            return false;
        } elseif (strstr($configurationString, key($item))) {
            // marker does not exist
            return false;
        } else {
            return true;
        }
    }

    protected function hasComposerLockChanged()
    {
        echo "TBD";
        //        $rootPath = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
        //        $lockFile = $rootPath.'composer.lock';
        //        $hashFile = $rootPath.'composer.lock.hash';
        //
        //        function msg() {
        //            echo "\n================\n";
        //            echo "Important Notice\n";
        //            echo "================\n\n";
        //            echo "Your composer.lock file has been updated, please run \n\n  php composer.phar install\n\nto ensure vendor folder integrity!\n";
        //            echo "\n";
        //        }
        //
        //        $currentHash = md5_file($lockFile);
        //        if (is_file($hashFile)) {
        //            $lastHash = file_get_contents($hashFile);
        //            echo "\nVerifying composer Installation";
        //            echo "\nLast Install Hash   : ".$lastHash;
        //            echo "\nCurrent Install Hash: ".$currentHash;
        //            if ($lastHash != $currentHash) {
        //                msg();
        //            }
        //        } else {
        //            msg();
        //        }
        //
        //        echo "\n";
        //
        //        file_put_contents($hashFile,$currentHash);
    }
}