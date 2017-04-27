<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\User;

require_once('./Services/Mail/classes/class.ilMail.php');

/**
 * Class InternalMailSender
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification
 */
class InternalMail {

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
	 * @param User|\ilObjRole $user_or_role
	 * @return $this
	 */
	public function cc($user_or_role) {
		if ($user_or_role instanceof User) {
			$this->cc[] = $user_or_role->getLogin();
		} else if ($user_or_role instanceof \ilObjRole) {
			$this->cc[] = "#" . $user_or_role->getTitle();
		}
		return $this;
	}

	/**
	 * @param User|\ilObjRole $user_or_role
	 * @return $this
	 */
	public function bcc($user_or_role) {
		if ($user_or_role instanceof User) {
			$this->cc[] = $user_or_role->getLogin();
		} else if ($user_or_role instanceof \ilObjRole) {
			$this->cc[] = "#" . $user_or_role->getTitle();
		}
		return $this;
	}

	/**
	 * @return bool
	 * @throws \ilException
	 */
	public function send() {
		$mailer = new \ilMail($this->sender->getId());
		$mailer->setSaveInSentbox(true);
		$result = $mailer->sendMail(
			$this->receiver->getLogin(),
			implode(',', $this->cc),
			implode(',', $this->bcc),
			$this->subject,
			$this->body,
			array(),
			array('normal')
		);
		if ($result) {
			$message = (is_array($result)) ? implode(', ', $result) : $result;
			throw new \ilException("Failed to send mail with error: " . $message);
		}
		return true;
	}

}