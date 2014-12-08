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
            exec($cmd.' 2>&1', $output, $return);
            if ($return == 0) {
                $this->_composerExecutable = $cmd;
                return;
            }
        }
        throw new Exception('Composer executable not found.');
    }

    protected function composer($command)
    {
        echo "\nComposing '$command'...\n";
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
        echo "\nRunning action '$command'...\n";
        \Yii::$app->runAction($command, $params);
    }
}
