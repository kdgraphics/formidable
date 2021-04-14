<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Exceptions\ScheduleException;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Notification;
use Ryssbowh\Formidable\Models\Records\Email;
use Ryssbowh\Formidable\Models\Records\Notification as NotificationRecord;
use Ryssbowh\Formidable\Models\Records\Spam;
use Ryssbowh\Formidable\Models\Records\Submission;
use craft\base\Component;
use craft\helpers\StringHelper;

class ScheduleService extends Component
{
	const STORAGE_PATH = '@root/storage/formidable';

	/**
	 * Create a new email schedule
	 * 
	 * @param  Spam|Submission|null $submitted
	 * @param  string|array         $sentFrom
	 * @param  array                $emails
	 * @param  string               $subject
	 * @param  string               $html
	 * @param  string               $type
	 * @return bool
	 */
	public function create($submitted, $sentFrom, array $emails, string $subject, string $html, string $type): bool
	{
		$email = new Email([
			'emails' => $emails,
			'submission_id' => ($submitted instanceof Submission) ? $submitted->id : null,
			'spam_id' => ($submitted instanceof Spam) ? $submitted->id : null,
			'sentFrom' => is_array($sentFrom) ? $sentFrom : [$sentFrom],
			'subject' => $subject,
			'type' => $type,
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
			return 0;
		}
		$submitted = null;
		if ($email->submission_id) {
			$submitted = Submission::find()->where(['id' => $email->submission_id])->one();
		} elseif ($email->spam_id) {
			$submitted = Spam::find()->where(['id' => $email->spam_id])->one();
		}
		$html = file_get_contents($contentFile);
		if (Formidable::$plugin->notifications->sendEmail($submitted, $email->getSentFrom(), $email->getEmails(), $email->subject, $html, $email->type)) {
			$email->sent = new \Datetime;
			$email->save();
			@unlink($contentFile);
		}
		return sizeof($email->getEmails());
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