<?php

/**
 * Class srDCLNotificationsConfigFormGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class srDCLNotificationsConfigFormGUI extends ilPropertyFormGUI
{

	/**
	 * @var ilDCLNotificationsConfigGUI
	 */
	protected $parent_gui;
	/**
	 * @var  ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilDCLNotificationsPlugin
	 */
	protected $pl;
	/**
	 * @var ilLanguage
	 */
	protected $lng;


	/**
	 * @param srDCLNotificationsConfigFormGUI $parent_gui
	 */
	public function __construct($parent_gui)
	{
		global $ilCtrl, $lng;
		$this->parent_gui = $parent_gui;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->pl = ilDCLNotificationsPlugin::getInstance();
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
		$this->setTitle('DataCollection Notifications');
		$this->initForm();
	}


	/**
	 * @param $field
	 *
	 * @return string
	 */
	public function txt($field)
	{
		return $this->pl->txt('admin_form_' . $field);
	}


	protected function initForm()
	{
		global $tpl;

		$tpl->addInlineCss("textarea {min-width: 520px !important;}");

		$multiinput = new srMultiLineInputGUI("DataCollections", srDCLNotificationsConfig::F_DCL_CONFIG);
		$multiinput->setInfo($this->txt(srDCLNotificationsConfig::F_DCL_CONFIG . '_info'));
		$multiinput->setTemplateDir($this->pl->getDirectory());

		$ref_id_item = new ilTextInputGUI('Datacollection Ref-ID', srDCLNotificationsConfig::F_DCL_REF_ID);
		$multiinput->addInput($ref_id_item);

		$table_id_item = new ilTextInputGUI('Datacollection Table-ID', srDCLNotificationsConfig::F_DCL_TABLE_ID);
		$multiinput->addInput($table_id_item);

		$mail_field = new ilTextInputGUI('Mail Field ID / E-Mail', srDCLNotificationsConfig::F_MAIL_FIELD_ID);
		$multiinput->addInput($mail_field);

		$base_lang_key_field = new ilTextInputGUI('E-Mail Key', srDCLNotificationsConfig::F_BASE_LANG_KEY);
		$multiinput->addInput($base_lang_key_field);

		$send_mail_field = new ilTextInputGUI('Send Mail Field ID', srDCLNotificationsConfig::F_SEND_MAIL_CHECK_FIELD_ID);
		$multiinput->addInput($send_mail_field);

		$send_mail_field_value = new ilTextInputGUI('Send Mail Field Value', srDCLNotificationsConfig::F_SEND_MAIL_CHECK_FIELD_VALUE);
		$multiinput->addInput($send_mail_field_value);

		$event_drop = new ilSelectInputGUI('Event', srDCLNotificationsConfig::F_SEND_MAIL_EVENT);
		$event_drop->setOptions([
			0 => $this->lng->txt('all'),
			"createRecord" => "createRecord",
			"updateRecord" => "updateRecord",
			"deleteRecord" => "deleteRecord"
		]);
		$multiinput->setShowLabel(true);
		$multiinput->addInput($event_drop);

		$this->addItem($multiinput);

		$multiinput_email = new srMultiLineInputGUI("Mail-Text", srDCLNotificationsConfig::F_DCL_MAIL_CONFIG);
		$multiinput_email->setInfo($this->txt(srDCLNotificationsConfig::F_DCL_MAIL_CONFIG . '_info'));
		$multiinput_email->setTemplateDir($this->pl->getDirectory());

		$language_key = new ilTextInputGUI('Mail-Text-Key', srDCLNotificationsConfig::F_DCL_MAIL_KEY);
		$multiinput_email->addInput($language_key);

		$mail_target = new ilSelectInputGUI('Mail-Target', srDCLNotificationsConfig::F_DCL_MAIL_TARGET);
		$mail_target->setOptions(array('owner'=>'Besitzer', 'extern'=>'Externer'));
		$multiinput_email->addInput($mail_target);

		$mail_subject= new ilTextInputGUI('Mail-Subject', srDCLNotificationsConfig::F_DCL_MAIL_SUBJECT);
		$multiinput_email->addInput($mail_subject);

		$mail_body = new ilDCLNotificationsTextAreaInputGUI('Mail-Body', srDCLNotificationsConfig::F_DCL_MAIL_BODY);
		$mail_body->setRows(10);
		$mail_body->setCols(50);
		$multiinput_email->setShowLabel(true);
		$multiinput_email->addInput($mail_body);

		$this->addItem($multiinput_email);

		$this->addCommandButtons();
	}


	public function fillForm()
	{
		$array = array();
		foreach ($this->getItems() as $item) {
			$this->getValuesForItem($item, $array);
		}
		$this->setValuesByArray($array);
	}


	/**
	 * @param ilFormPropertyGUI $item
	 * @param                   $array
	 *
	 * @internal param $key
	 */
	private function getValuesForItem($item, &$array)
	{
		if (self::checkItem($item)) {
			$key = $item->getPostVar();
			$array[$key] = srDCLNotificationsConfig::getConfigValue($key);
			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->getValuesForItem($subitem, $array);
				}
			}
		}
	}


	/**
	 * @return bool
	 */
	public function saveObject()
	{
		if (!$this->checkInput()) {
			return false;
		}
		foreach ($this->getItems() as $item) {
			$this->saveValueForItem($item);
		}

		return true;
	}


	/**
	 * @param  ilFormPropertyGUI $item
	 */
	private function saveValueForItem($item)
	{
		if (self::checkItem($item)) {
			$key = $item->getPostVar();

			srDCLNotificationsConfig::set($key, $this->getInput($key));
			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->saveValueForItem($subitem);
				}
			}
		}
	}


	/**
	 * @param $item
	 *
	 * @return bool
	 */
	public static function checkForSubItem($item)
	{
		return !$item instanceof ilFormSectionHeaderGUI AND !$item instanceof ilMultiSelectInputGUI and !$item instanceof ilEMailInputGUI;
	}


	/**
	 * @param $item
	 *
	 * @return bool
	 */
	public static function checkItem($item)
	{
		return !$item instanceof ilFormSectionHeaderGUI;
	}


	protected function addCommandButtons()
	{
		$this->addCommandButton('save', $this->lng->txt('save'));
		$this->addCommandButton('cancel', $this->lng->txt('cancel'));
	}
}