<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Exceptions\ScheduleException;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Notification;
use Ryssbowh\Formidable\Models\Records\Email;
use Ryssbowh\Formidable\Models\Records\Submission;
use craft\base\Component;
use craft\helpers\StringHelper;

class ScheduleService extends Component
{
	const STORAGE_PATH = '@root/storage/formidable';

	/**
	 * Create a new email schedule
	 * 
	 * @param  Submission $submission
	 * @param  string     $sentFrom
	 * @param  array      $emails
	 * @param  string     $subject
	 * @param  string     $html
	 * @param  bool       $forAdmin
	 * @return bool
	 */
	public function create(Submission $submission, string $sentFrom, array $emails, string $subject, string $html, bool $forAdmin): bool
	{
		$email = new Email([
			'emails' => $emails,
			'submission_id' => $submission->id,
			'sentFrom' => $sentFrom,
			'forAdmin' => $forAdmin,
			'subject' => $subject,
			'uid' => $this->createStorageFile($html)
		]);
		return $email->save(false);
	}

	/**
	 * Get all email unsent
	 * 
	 * @return array
	 */
	public function getUnsent(): array
	{
		return Email::find()->where(['sent' => null])->all();
	}

	/**
	 * Sends a scheduled email
	 * 
	 * @param  Email  $email
	 * @throws ScheduleException
	 * @return int
	 */
	public function send(Email $email): int
	{
		$contentFile = \Craft::getAlias(self::STORAGE_PATH.'/'.$email->uid);
		if (!file_exists($contentFile)) {
			throw ScheduleException::storageNotFound($email, $contentFile);
		}
		$submission = Submission::find()->where(['id' => $email->submission_id])->one();
		if (!$submission) {
			throw ScheduleException::submissionNotFound($email, $email->submission_id);
		}
		$html = file_get_contents($contentFile);
		if (Formidable::$plugin->notifications->sendEmail($submission, $email->sentFrom, $email->emails, $email->subject, $html, $email->forAdmin)) {
			$email->sent = new \Datetime;
			$email->save();
			@unlink($contentFile);
		}
		return sizeof($email->emails);
	}

	/**
	 * Save the html content on disk so a schedule can pick it up later
	 * 
	 * @param  string $html
	 * @throws ScheduleException
	 * @return string
	 */
	protected function createStorageFile(string $html): string
	{
		$path = \Craft::getAlias(self::STORAGE_PATH);
		if (!is_dir($path)) {
			mkdir($path, 0755, true);
		}
		if (!is_dir($path)) {
			throw ScheduleException::path($path);
		}
		$uid = StringHelper::UUID();
		if (!file_put_contents($path.'/'.$uid, $html)) {
			throw ScheduleException::perms($path.'/'.$uid);
		}
		return $uid;
	}
}