<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * profile actions.
 *
 * @package    OpenPNE
 * @subpackage profile
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class profileActions extends sfActions
{
 /**
  * Executes list action
  *
  * @param sfRequest $request A request object
  */
  public function executeList($request)
  {
    $this->profiles = Doctrine::getTable('Profile')->retrievesAll();

    // NOTE: for editOption action
    if (!isset($this->optionForms))
    {
      $this->optionForms = array();
    }

    foreach ($this->profiles as $profile)
    {
      if (!isset($this->optionForms[$profile->getId()]))
      {
        $form = new opProfileOptionsForm();
        $form->setProfile($profile);
        $this->optionForms[$profile->getId()] = $form;
      }
    }
  }

 /**
  * Executes edit action
  *
  * @param sfRequest $request A request object
  */
  public function executeEdit($request)
  {
    $this->profile = Doctrine::getTable('Profile')->find($request->getParameter('id'));
    $this->form = new ProfileForm($this->profile);
    $this->presetForm = new opPresetProfileForm($this->profile);

    if ($request->isMethod('post'))
    {
      $form = $this->form;
      if ('preset' === $request->getParameter('type'))
      {
        $form = $this->presetForm;
      }

      $parameter = $request->getParameter('profile');
      if ($form->getObject()->isNew())
      {
        $parameter['sort_order'] = Doctrine::getTable('Profile')->getMaxSortOrder();
      }

      $form->bind($parameter);
      if ($form->isValid())
      {
        $form->save();
        $this->redirect('profile/list');
      }
    }
  }

 /**
  * Executes editOption action
  *
  * @param sfRequest $request A request object
  */
  public function executeEditOption($request)
  {
    $profile = Doctrine::getTable('Profile')->find($request->getParameter('id'));
    $this->forward404Unless($profile);

    $form = new opProfileOptionsForm();
    $form->setProfile($profile);
    if ($form->bindAndSave($request->getParameter('profile_options')))
    {
      $this->redirect('profile/list#'.$profile->getName());
    }

    $this->optionForms = array();
    $this->optionForms[$profile->getId()] = $form;
    $this->executeList($request);
    $this->setTemplate('list');
  }

 /**
  * Executes delete action
  *
  * @param sfRequest $request A request object
  */
  public function executeDelete($request)
  {
    $this->profile = Doctrine::getTable('Profile')->find($request->getParameter('id'));
    $this->forward404Unless($this->profile);

    if ($request->isMethod('post')) {
      $this->profile->delete();
      $this->redirect('profile/list');
    }
  }

 /**
  * Executes deleteOption action
  *
  * @param sfRequest $request A request object
  */
  public function executeDeleteOption($request)
  {
    $this->profileOption = Doctrine::getTable('ProfileOption')->find($request->getParameter('id'));
    $this->forward404Unless($this->profileOption);

    if ($request->isMethod('post')) {
      $this->profileOption->delete();
    }
    $this->redirect('profile/list');
  }

  /**
   * Executes sortProfile action
   *
   * @param sfRequest $request A request object
   */
  public function executeSortProfile($request)
  {
    if ($request->isXmlHttpRequest())
    {
      $order = $request->getParameter('profiles');
      for ($i = 0; $i < count($order); $i++)
      {
        $profile = Doctrine::getTable('Profile')->find($order[$i]);
        if ($profile)
        {
          $profile->setSortOrder($i * 10);
          $profile->save();
        }
      }
    }
    return sfView::NONE;
  }
}
