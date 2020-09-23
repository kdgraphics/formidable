<?php 

namespace Ryssbowh\Formidable\Console;

use Ryssbowh\Formidable\Formidable;
use craft\console\Controller;
use yii\console\ExitCode;

class SendEmailsController extends Controller
{
	const LOCK_FILE = '@root/storage/formidable/lock';

	/**
	 * Sends the emails saved in database
	 * @return int
	 */
	public function actionIndex()
	{
		if ($this->isSending()) {
			$this->stdout("Aborting : lock file exists, is the cron running already ?\n");
			return ExitCode::OK;
		}
		$this->lock();
		try {
			$schedule = Formidable::$plugin->schedule;
			$emails = $schedule->getUnsent();
			$sent = 0;
			foreach ($emails as $email) {
				$this->stdout("Sending email ".$email->uid." to ".implode(', ', $email->emails)."...\n");
				$sent += $schedule->send($email);
			}
		} catch (Exception $e) {
			$this->stderr('Error : '.$e->getMessage()."\n");
		}
		$this->unlock();
		$this->stdout("Finished : $sent emails sent\n");
		return ExitCode::OK;
	}

	/**
	 * Write the lock file so processes dont overlap
	 */
	protected function lock()
	{
		$file = \Craft::getAlias(self::LOCK_FILE);
		$folder = dirname($file);
		if (!is_dir($folder)) {
			mkdir($folder, 0755, true);
		}
		file_put_contents($file, time());
	}

	/**
	 * Delete the lock file
	 */
	protected function unlock()
	{
		unlink(\Craft::getAlias(self::LOCK_FILE));
	}

	/**
	 * Does the lock file exist
	 * 
	 * @return boolean
	 */
	protected function isSending()
	{
		return file_exists(\Craft::getAlias(self::LOCK_FILE));
	}
}