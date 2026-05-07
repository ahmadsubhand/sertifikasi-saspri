<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Database tools.
 */
class DbController extends Controller
{
    /**
     * Seeds the database with all SQL files found in console/db directory.
     */
    public function actionSeed()
    {
        $dbDir = Yii::getAlias('@console/db');
        $files = glob($dbDir . '/*.sql');

        if (empty($files)) {
            $this->stdout("No SQL files found in $dbDir\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Sort files alphabetically to ensure consistent execution order
        sort($files);

        $db = Yii::$app->db;

        foreach ($files as $file) {
            $fileName = basename($file);
            $this->stdout("Executing $fileName... ");

            $sql = file_get_contents($file);
            if ($sql === false) {
                $this->stdout("FAILED (Could not read file)\n", Console::FG_RED);
                continue;
            }

            $transaction = $db->beginTransaction();
            try {
                // PDO::exec or createCommand()->execute() can handle multiple statements
                // in one string for MySQL if the driver allows it.
                $db->createCommand($sql)->execute();
                $transaction->commit();
                $this->stdout("DONE\n", Console::FG_GREEN);
            } catch (\Exception $e) {
                $transaction->rollBack();
                $this->stdout("ERROR\n", Console::FG_RED);
                $this->stdout($e->getMessage() . "\n", Console::FG_YELLOW);
            }
        }

        $this->stdout("\nSeeding process completed.\n", Console::FG_CYAN);
        return ExitCode::OK;
    }
}
