<?php

require_once __DIR__ . "/../vendor/autoload.php";

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Form\CourseConfigFormGUI;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Form\NotificationConfigFormGUI;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourse;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourseQuery;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\LearningObjectiveCourseTableGUI;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\TwigParser;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\StudyProgramQuery;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestionsUI\SuggestionsTableGUI;

/**
 * Class ilLearningObjectiveSuggestionsConfigGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilLearningObjectiveSuggestionsConfigGUI extends ilPluginConfigGUI {

	const CMD_ADD_COURSE = "addCourse";
	const CMD_CANCEL = "cancel";
	const CMD_CONFIGURE = "configure";
	const CMD_CONFIGURE_COURSE = "configureCourse";
	const CMD_CONFIRM_DELETE_COURSE_CONFIG = "confirmDeleteCourse";
	const CMD_DELETE_COURSE = "deleteCourse";
	const CMD_DOWNLOAD_SUGGESTIONS = "downloadSuggestions";
	const CMD_CONFIGURE_NOTIFICATIONS = "configureNotifications";
	const CMD_CONFIGURE_NOTIFICATIONS_USERS_AUTOCOMPLETE = "configureNotificationsUsersAutocomplete";
	const CMD_CONFIGURE_NOTIFICATIONS_ROLES_AUTOCOMPLETE = "configureNotificationsRolesAutocomplete";
	const CMD_SAVE = "save";
	const CMD_SAVE_COURSE = "saveCourse";
	const CMD_SAVE_NOTIFICATIONS = "saveNotifications";
	const TAB_CONFIGURE_COURSE = "configureCourse";
	const TAB_CONFIGURE_NOTIFICATIONS = "configureNotifications";
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilRbacReview
	 */
	protected $rbacreview;
	/**
	 * @var ilLearningObjectiveSuggestionsPlugin
	 */
	protected $pl;
	/**
	 * @var ilTree
	 */
	protected $tree;


	/**
	 *
	 */
	public function __construct() {
		global $DIC;
		$this->tpl = $DIC->ui()->mainTemplate();
		$this->ctrl = $DIC->ctrl();
		$this->tabs = $DIC->tabs();
		$this->toolbar = $DIC->toolbar();
		$this->rbacreview = $DIC->rbac()->review();
		$this->pl = ilLearningObjectiveSuggestionsPlugin::getInstance();
		$this->tree = $DIC->repositoryTree();
	}


	/**
	 * @param string $cmd
	 */
	function performCommand($cmd) {
		$this->ctrl->saveParameter($this, 'course_ref_id');
		$this->$cmd();
	}


	/**
	 *
	 */
	protected function configure() {
		$button = ilLinkButton::getInstance();
		$button->setCaption($this->pl->txt("add_course"), false);
		$button->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD_COURSE));
		$this->toolbar->addButtonInstance($button);
		$table = new LearningObjectiveCourseTableGUI($this);
		$query = new LearningObjectiveCourseQuery(new ConfigProvider());
		$table->setCourses($query->getAll());
		$this->tpl->setContent($table->getHTML());
	}


	/**
	 *
	 */
	protected function configureCourse() {
		$this->addTabs(self::TAB_CONFIGURE_COURSE);
		$this->tabs->setBackTarget($this->pl->txt("back"), $this->ctrl->getLinkTarget($this, self::CMD_CONFIGURE));
		$course = new LearningObjectiveCourse(new ilObjCourse((int)$_GET['course_ref_id']));
		$this->initCourseHeader($course);
		$config = new CourseConfigProvider($course);
		$form = new CourseConfigFormGUI($config, new LearningObjectiveQuery($config), new StudyProgramQuery($config));
		$form->setFormAction($this->ctrl->getFormAction($this));
		$this->tpl->setContent($form->getHTML());
	}


	protected function confirmDeleteCourse() {
		global $DIC;

		$this->ctrl->saveParameter($this, 'course_ref_id');
		$confirmation_gui = new ilConfirmationGUI();
		$confirmation_gui->setFormAction($this->ctrl->getFormAction($this));

		$confirmation_gui->setConfirm($this->pl->txt('delete_learning_objective_course'), self::CMD_DELETE_COURSE, self::CMD_DELETE_COURSE);

		$this->ctrl->setParameter($this, 'cmd', self::CMD_CANCEL);
		$confirmation_gui->setCancel($this->pl->txt('cancel'), self::CMD_CANCEL, self::CMD_CANCEL);

		$confirmation_gui->setHeaderText($this->pl->txt('confirm_delete_crs') . " " . $DIC->ui()->renderer()->render($DIC->ui()->factory()->link()
				->standard($this->pl->txt('download_suggestions'), $this->ctrl->getLinkTarget($this, self::CMD_DOWNLOAD_SUGGESTIONS))));

		$this->tpl->setContent($confirmation_gui->getHTML());
	}


	protected function deleteCourse() {

		$course = new LearningObjectiveCourse(new ilObjCourse((int)$_GET['course_ref_id']));
		$config = new ConfigProvider();
		$course_config = new CourseConfigProvider($course);

		$ref_ids = (array)json_decode($config->get('course_ref_ids'), true);
		if (($key = array_search($_GET['course_ref_id'], $ref_ids)) !== false) {
			unset($ref_ids[$key]);
		}
		$config->set('course_ref_ids', json_encode(array_unique($ref_ids)));

		$course_config->delete();

		ilUtil::sendSuccess($this->pl->txt("course_removed"),true);
		$this->ctrl->redirect($this, self::CMD_CONFIGURE);
	}


	protected function downloadSuggestions() {

		$this->ctrl->setParameterByClass("alouiCourseGUI", 'alo_xpt', 1);
		$this->ctrl->setParameterByClass("alouiCourseGUI", 'ref_id', $_GET['course_ref_id']);
		$this->ctrl->redirectByClass([ "ilUIPluginRouterGUI", "alouiCourseGUI" ]);
	}


	/**
	 *
	 */
	protected function configureNotifications() {
		$this->addTabs(self::TAB_CONFIGURE_NOTIFICATIONS);
		$course = new LearningObjectiveCourse(new ilObjCourse((int)$_GET['course_ref_id']));
		$this->initCourseHeader($course);
		$this->tabs->setBackTarget($this->pl->txt("back"), $this->ctrl->getLinkTarget($this, self::CMD_CONFIGURE));
		$form = new NotificationConfigFormGUI(new CourseConfigProvider($course), new TwigParser());
		$form->setFormAction($this->ctrl->getFormAction($this));
		$this->tpl->setContent($form->getHTML());
	}


	/**
	 *
	 */
	protected function configureNotificationsUsersAutocomplete() {
		$term = filter_input(INPUT_GET, "term");

		// Get users
		$autocomplete = new ilUserAutoComplete();

		$autocomplete->setSearchFields([ "usr_id", "login", "firstname", "lastname", "email" ]);
		$autocomplete->setResultField("usr_id");
		$autocomplete->enableFieldSearchableCheck(false);

		$users = json_decode($autocomplete->getList($term));

		// Format label to lastname, firstname, login
		$users->items = array_map(function (stdClass $user) {
			$labels = preg_split("/[(, )( \[)]/", $user->label, - 1, PREG_SPLIT_NO_EMPTY);
			$labels[2] = substr($labels[2], 0, - 1);
			$labels = [ $labels[0], $labels[1], $labels[2] ];

			return [
				"label" => $labels,
				"value" => $user->value
			];
		}, $users->items);

		// Sort users by labels
		usort($users->items, function (array $user1, array $user2) {
			foreach (array_keys($user1["label"]) as $i) {
				$sort1 = strtolower($user1["label"][$i]);
				$sort2 = strtolower($user2["label"][$i]);

				if ($sort1 > $sort2) {
					return 1;
				}
				if ($sort1 < $sort2) {
					return - 1;
				}
			}

			return 0;
		});

		// Join labels
		$users->items = array_map(function (array $user) {
			$user["label"] = implode(", ", $user["label"]);

			return $user;
		}, $users->items);

		// Output
		echo json_encode($users);

		exit();
	}


	/**
	 *
	 */
	protected function configureNotificationsRolesAutocomplete() {
		$term = filter_input(INPUT_GET, "term");

		// Get roles
		//$roles = json_encode(ilRoleAutoComplete::getList($term)); // ilRoleAutoComplete is bad and not so good configurable like ilUserAutoComplete
		/**
		 * @var array $roles
		 */
		$roles = $this->rbacreview->getRolesForIDs([ $term ], false); // Allow search for role id
		if (count($roles) === 0 || !empty(ilObject::_lookupDeletedDate($roles[0]["parent"]))) {
			$roles = $this->rbacreview->getRolesByFilter(ilRbacReview::FILTER_ALL, 0, trim($term));
		}

		// Remove roles of deleted parent
		$roles = array_filter($roles, function (array $role) {
			return empty(ilObject::_lookupDeletedDate($role["parent"])); // TODO move this to core rbacreview check
		});

		$roles = array_map(function (array $role) {
			$labels = [
				ilObjRole::_getTranslation($role["title"])
			];
			if ($role["role_type"] === "local") {
				/**
				 * @var ilObjCourse|ilObject $parent
				 */
				$parent = ilObjectFactory::getInstanceByRefId($role["parent"]);
				$labels[] = $parent->getTitle();

				/**
				 * @var ilObjCategory|ilObject $parent_parent
				 */
				$parent_parent = ilObjectFactory::getInstanceByRefId($this->tree->getParentId($parent->getRefId()));
				$labels[] = $parent_parent->getTitle();
			}

			return [
				"label" => $labels,
				"value" => $role["obj_id"]
			];
		}, $roles);

		// Sort roles by labels
		usort($roles, function (array $role1, array $role2) {
			foreach (array_keys($role1["label"]) as $i) {
				$sort1 = strtolower($role1["label"][$i]);
				$sort2 = strtolower($role2["label"][$i]);

				if ($sort1 > $sort2) {
					return 1;
				}
				if ($sort1 < $sort2) {
					return - 1;
				}
			}

			return 0;
		});

		// Join labels
		$roles = array_map(function (array $role) {
			$role["label"] = implode(", ", $role["label"]);

			return $role;
		}, $roles);

		// Output
		$roles = [
			"items" => $roles,
			"hasMoreResults" => false
		];

		echo json_encode($roles);

		exit();
	}


	/**
	 * @param LearningObjectiveCourse $course
	 */
	protected function initCourseHeader(LearningObjectiveCourse $course) {
		$this->tpl->setTitle($course->getTitle());
		$this->tpl->setTitleIcon(ilUtil::getTypeIconPath('crs', $course->getId(), 'big'));
	}


	/**
	 *
	 */
	protected function addCourse() {
		$form = $this->getAddCourseFormGUI();
		$this->tpl->setContent($form->getHTML());
	}


	/**
	 *
	 */
	protected function saveCourse() {
		$form = $this->getAddCourseFormGUI();
		if ($form->checkInput() && ilObject::_lookupType($form->getInput('ref_id'), true) == 'crs') {
			$config = new ConfigProvider();
			$ref_ids = (array)json_decode($config->get('course_ref_ids'), true);
			$ref_ids[] = $form->getInput('ref_id');
			$config->set('course_ref_ids', json_encode(array_unique($ref_ids)));
			ilUtil::sendSuccess($this->pl->txt("course_added"));
			$this->ctrl->redirect($this, self::CMD_CONFIGURE);
		}
		if (ilObject::_lookupType($form->getInput('ref_id'), true) != 'crs') {
			$form->getItemByPostVar('ref_id')->setAlert($this->pl->txt("no_course_object"));
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}


	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getAddCourseFormGUI() {
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->pl->txt("create_course"));
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton(self::CMD_SAVE_COURSE, $this->pl->txt("save"));
		$form->addCommandButton(self::CMD_CANCEL, $this->pl->txt("cancel"));
		$item = new ilNumberInputGUI($this->pl->txt("ref_id"), 'ref_id');
		$item->setInfo($this->pl->txt("ref_id_info"));
		$item->setRequired(true);
		$form->addItem($item);

		return $form;
	}


	/**
	 *
	 */
	protected function cancel() {
		$this->configure();
	}


	/**
	 * @param CourseConfigProvider $config
	 * @param ilPropertyFormGUI    $form
	 */
	protected function storeConfig($config, ilPropertyFormGUI $form) {
		foreach ($form->getItems() as $item) {
			/** @var ilFormPropertyGUI $item */
			$value = $form->getInput($item->getPostVar());
			if ($value === NULL) {
				continue;
			}
			$value = (is_array($value)) ? json_encode($value) : $value;
			$config->set($item->getPostVar(), $value);
		}
	}


	/**
	 *
	 */
	protected function saveNotifications() {
		$this->addTabs(self::TAB_CONFIGURE_NOTIFICATIONS);
		$course = new LearningObjectiveCourse(new ilObjCourse((int)$_GET['course_ref_id']));
		$config = new CourseConfigProvider($course);
		$this->tabs->setBackTarget($this->pl->txt("back"), $this->ctrl->getLinkTarget($this, self::CMD_CONFIGURE));
		$this->initCourseHeader($course);
		$form = new NotificationConfigFormGUI($config, new TwigParser());
		if ($form->checkInput()) {
			$this->storeConfig($config, $form);
			ilUtil::sendSuccess($this->pl->txt("configuration_saved"), true);
			$this->ctrl->redirect($this, self::CMD_CONFIGURE_NOTIFICATIONS);
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}


	/**
	 *
	 */
	protected function save() {
		$this->addTabs(self::TAB_CONFIGURE_COURSE);
		$course = new LearningObjectiveCourse(new ilObjCourse((int)$_GET['course_ref_id']));
		$this->tabs->setBackTarget($this->pl->txt("back"), $this->ctrl->getLinkTarget($this, self::CMD_CONFIGURE));
		$this->initCourseHeader($course);
		$config = new CourseConfigProvider($course);
		$form = new CourseConfigFormGUI($config, new LearningObjectiveQuery($config), new StudyProgramQuery($config));
		if ($form->checkInput()) {
			$this->storeConfig($config, $form);
			ilUtil::sendSuccess($this->pl->txt("configuration_saved"), true);
			$this->ctrl->redirect($this, self::CMD_CONFIGURE_COURSE);
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}


	/**
	 * @param string $active
	 */
	protected function addTabs($active = '') {
		$this->tabs->addTab(self::TAB_CONFIGURE_COURSE, $this->pl->txt("basic_configuration"), $this->ctrl->getLinkTarget($this, self::CMD_CONFIGURE_COURSE));
		$this->tabs->addTab(self::TAB_CONFIGURE_NOTIFICATIONS, $this->pl->txt("notifications"), $this->ctrl->getLinkTarget($this, self::CMD_CONFIGURE_NOTIFICATIONS));
		if ($active) {
			$this->tabs->activateTab($active);
		}
	}
}
