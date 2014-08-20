<?php

/**
 * @version     3.0.0
 * @package     com_imc
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */
// No direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Issue controller class.
 */
class ImcControllerIssueForm extends ImcController {

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @since	1.6
     */
    public function edit() {
        $app = JFactory::getApplication();

        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $app->getUserState('com_imc.edit.issue.id');
        $editId = JFactory::getApplication()->input->getInt('id', null, 'array');

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_imc.edit.issue.id', $editId);

        // Get the model.
        $model = $this->getModel('IssueForm', 'ImcModel');

        // Check out the item
        if ($editId) {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId) {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(JRoute::_('index.php?option=com_imc&id=&view=issue&layout=edit', false));
    }

    /**
     * Method to save a user's profile data.
     *
     * @return	void
     * @since	1.6
     */
    public function save() {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app = JFactory::getApplication();
        $model = $this->getModel('IssueForm', 'ImcModel');

        // Get the user data.
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');

        // Validate the posted data.
        $form = $model->getForm();
        if (!$form) {
            JError::raiseError(500, $model->getError());
            return false;
        }

        // Validate the posted data.
        $data = $model->validate($form, $data);

        // Check for errors.
        if ($data === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            $input = $app->input;
            $jform = $input->get('jform', array(), 'ARRAY');

            // Save the data in the session.
            $app->setUserState('com_imc.edit.issue.data', $jform, array());

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_imc.edit.issue.id');
            $this->setRedirect(JRoute::_('index.php?option=com_imc&view=issueform&layout=edit&id=' . $id, false));
            return false;
        }

        // Attempt to save the data.
        $return = $model->save($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $app->setUserState('com_imc.edit.issue.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_imc.edit.issue.id');
            $this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_imc&view=issueform&layout=edit&id=' . $id, false));
            return false;
        }


        // Check in the profile.
        if ($return) {
            $model->checkin($return);
        }

        // Clear the profile id from the session.
        $app->setUserState('com_imc.edit.issue.id', null);

        // Redirect to the list screen.
        $this->setMessage(JText::_('COM_IMC_ITEM_SAVED_SUCCESSFULLY'));
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_imc&view=issues' : $item->link);
        $this->setRedirect(JRoute::_($url, false));

        // Flush the data from the session.
        $app->setUserState('com_imc.edit.issue.data', null);

        //emulate postSaveHook like extending from JControllerForm
        $this->postSaveHook($model, $data);
    }

    function cancel() {
        
        $app = JFactory::getApplication();

        // Get the current edit id.
        $editId = (int) $app->getUserState('com_imc.edit.issue.id');

        // Get the model.
        $model = $this->getModel('IssueForm', 'ImcModel');

        // Check in the item
        if ($editId) {
            $model->checkin($editId);
        }
        
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_imc&view=issues' : $item->link);
        $this->setRedirect(JRoute::_($url, false));
    }

    public function remove() {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app = JFactory::getApplication();
        $model = $this->getModel('IssueForm', 'ImcModel');

        // Get the user data.
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');

        // Validate the posted data.
        $form = $model->getForm();
        if (!$form) {
            JError::raiseError(500, $model->getError());
            return false;
        }

        // Validate the posted data.
        $data = $model->validate($form, $data);

        // Check for errors.
        if ($data === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState('com_imc.edit.issue.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_imc.edit.issue.id');
            $this->setRedirect(JRoute::_('index.php?option=com_imc&view=issue&layout=edit&id=' . $id, false));
            return false;
        }

        // Attempt to save the data.
        $return = $model->delete($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $app->setUserState('com_imc.edit.issue.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_imc.edit.issue.id');
            $this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_imc&view=issue&layout=edit&id=' . $id, false));
            return false;
        }


        // Check in the profile.
        if ($return) {
            $model->checkin($return);
        }

        // Clear the profile id from the session.
        $app->setUserState('com_imc.edit.issue.id', null);

        // Redirect to the list screen.
        $this->setMessage(JText::_('COM_IMC_ITEM_DELETED_SUCCESSFULLY'));
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_imc&view=issues' : $item->link);
        $this->setRedirect(JRoute::_($url, false));

        // Flush the data from the session.
        $app->setUserState('com_imc.edit.issue.data', null);
    }

    //simulate postSaveHook to move any images to the correct directory
    protected function postSaveHook(JModelLegacy $model, $data = array())
    {
        
        $insertid = JFactory::getApplication()->getUserState('com_imc.edit.issue.insertid');

        //A: inform log table about the new issue
        if($data['id'] == 0){
            

            JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
            $log = JTable::getInstance('Log', 'ImcTable', array());

            $data2['state'] = 1;
            $data2['action'] = JText::_('COM_IMC_LOGS_ACTION_INITIAL_COMMIT');
            $data2['issueid'] = $insertid; //$model->getItem()->get('id');
            $data2['stepid'] = $data['stepid'];
            $data2['description'] = JText::_('COM_IMC_LOGS_ACTION_INITIAL_COMMIT');
            $data2['created'] = $data['created'];
            $data2['created_by'] = $data['created_by'];
            $data2['updated'] = $data['created'];
            $data2['language'] = $data['language'];
            $data2['rules'] = $data['rules'];

            
            if (!$log->bind($data2))
            {
                JFactory::getApplication()->enqueueMessage('Cannot bind data to log table', 'error'); 
            }

            if (!$log->save($data2))
            {
                JFactory::getApplication()->enqueueMessage('Cannot save data to log table', 'error'); 
            }
            
        }

        //B: move any images only if record is new
        if($data['id'] > 0)
            return;

        //check if any files uploaded
        $obj = json_decode( $data['photo'] );
        if(empty($obj->files))
            return;

        $srcDir = JPATH_ROOT . '/' . $obj->imagedir . '/' . $obj->id;
        $dstDir = JPATH_ROOT . '/' . $obj->imagedir . '/' . $insertid;

        $success = rename ( $srcDir , $dstDir );

        if(!$success){
            JFactory::getApplication()->enqueueMessage('Cannot move '.$srcDir.' to '.$dstDir.'. Check folder rights', 'error'); 
        }
        
    }
}
