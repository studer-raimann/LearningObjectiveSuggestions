<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Notification;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\User\User;

require_once('./Services/Mail/classes/class.ilMail.php');

/**
 * Class InternalMailSender
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\Notification
 */
class InternalMailSender {

	/**
	 * @var User
	 */
	protected $sender;

	/**
	 * @var User
	 */
	protected $receiver;

	/**
	 * @var array
	 */
	protected $cc = array();

	/**
	 * @var array
	 */
	protected $bcc = array();

	/**
	 * @var string
	 */
	protected $subject = '';

	/**
	 * @var string
	 */
	protected $body = '';


	/**
	 * @param string $subject
	 * @return $this
	 */
	public function subject($subject) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @param string $body
	 * @return $this
	 */
	public function body($body) {
		$this->body = $body;
		return $this;
	}

	/**
	 * @param User $user
	 * @return $this
	 */
	public function from(User $user) {
		$this->sender = $user;
		return $this;
	}

	/**
	 * @param User $user
	 * @return $this
	 */
	public function to(User $user) {
		$this->receiver = $user;
		return $this;
	}

	/**
	 * @param User $user
	 * @return $this
	 */
	public function cc(User $user) {
		$this->cc[] = $user->getLogin();
		return $this;
	}

	/**
	 * @param User $user
	 * @return $this
	 */
	public function bcc(User $user) {
		$this->bcc[] = $user->getLogin();
		return $this;
	}

	/**
	 * @return bool
	 */
	public function send() {
		$mailer = new \ilMail($this->sender->getLogin());
		$mailer->setSaveInSentbox(true);
		return !$mailer->sendMail(
			$this->receiver->getLogin(),
			$this->cc,
			$this->bcc,
			$this->subject,
			$this->body,
			array(),
			array('normal')
		);
	}

}