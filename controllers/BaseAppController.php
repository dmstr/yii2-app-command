<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2014 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\console\controllers;

use mikehaertl\shellcommand\Command;
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
    public $defaultAction = 'version';

    protected $_composerExecutable = null;

    public function init()
    {
        parent::init();
        foreach ($this->composerExecutables AS $cmd) {
            // TODO: improve check
            exec($cmd.' 2>&1', $output, $return);
            if ($return == 0) {
                $this->_composerExecutable = $cmd;
                return;
            }
        }
        throw new Exception('Composer executable not found.');
    }

    /**
     * Displays application version from git describe and writes it to `version`
     */
    public function actionVersion()
    {
        echo "Application Version\n";
        $cmd = new Command("git describe --dirty");
        if ($cmd->execute()) {
            echo $cmd->getOutput();
            file_put_contents(\Yii::getAlias('@app/version'), $cmd->getOutput());
        } else {
            echo $cmd->getOutput();
            echo $cmd->getStdErr();
            echo $cmd->getError();
        }
        echo "\n";
    }

    /**
     * create MySQL database from ENV vars and grant permissions
     *
     * @param $db database name
     */
    public function actionCreateMysqlDb($db = null)
    {
        $root          = getenv("DB_ENV_MYSQL_ROOT_USER")?:'root';
        $root_password = getenv("DB_ENV_MYSQL_ROOT_PASSWORD");
        $user          = getenv("DB_ENV_MYSQL_USER");
        $pass          = getenv("DB_ENV_MYSQL_PASSWORD");
        $dsn           = getenv("DATABASE_DSN_BASE");

        if ($db === null) {
            $db            = getenv("DATABASE_DSN_DB");
        }
        try {
            // retry an operation up to 5 times
            $dbh = \igorw\retry(30, function () use ($dsn, $root, $root_password) {
                $this->stdout('.');
                sleep(1);
                return new \PDO($dsn, $root, $root_password);
            });
        } catch (FailingTooHardException $e) {
            die("Unable to connect to database: " . $e->getMessage());
        }

        try {
            $dbh->exec(
                "CREATE DATABASE IF NOT EXISTS `$db`;
         GRANT ALL ON `$db`.* TO '$user'@'%' IDENTIFIED BY '$pass';
         FLUSH PRIVILEGES;"
            )
            or die(print_r($dbh->errorInfo(), true));
        } catch (\PDOException $e) {
            die("DB ERROR: " . $e->getMessage());
        }

        $this->stdout("\nDatabase successfully created.\n");
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
