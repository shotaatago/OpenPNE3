<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opProfileOptionsForm
 *
 * @package    OpenPNE
 * @subpackage form
 * @author     Shogo Kawahara <kawahara@tejimaya.net>
 */
class opProfileOptionsForm extends BaseForm
{
  protected 
    $profile = null,
    $profileOptions = null;

 /**
  * set object of profile and generate widget and validator
  *
  * @param Profile $profile
  */
  public function setProfile(Profile $profile)
  {
    $this->profile = $profile;
    $this->profileOptions = array();

    foreach ($profile->getProfileOption() as $option)
    {
      $name = $option->getId();
      $this->profileOptions[$name] = $option;
      $this->setWidget($name, new sfWidgetFormInput());
      $this->setValidator($name, new opValidatorString(array('trim' => true)));
      $this->setWidget('is_delete_'.$name, new sfWidgetFormInputCheckbox());
      $this->setValidator('is_delete_'.$name, new sfValidatorBoolean());
      $this->setDefault($name, $option->getValue());
    }

    $name = 'new';
    $this->setWidget($name, new sfWidgetFormInput());
    $this->setValidator($name, new opValidatorString(array('trim' => true, 'required' => false)));

    $this->widgetSchema->setNameFormat('profile_options[%s]');
  }

  public function getProfile()
  {
    return $this->profile;
  }

  public function getProfileOptions()
  {
    return $this->profileOptions;
  }

 /**
  * save profile options
  */
  public function save()
  {
    if (null == $this->profile)
    {
      throw new LogicException('profile object is not set.');
    }

    $sortOrder = 0;
    foreach ($this->getValues() as $key => $value)
    {
      if (is_numeric($key))
      {
        $option = $this->profileOptions[$key];
        if ($option->exists())
        {
          $option->setValue($value);
          $option->setSortOrder($sortOrder++);
          $option->save();
        }
      }
      elseif ($value && preg_match('/^is_delete_(\d+)$/', $key, $match))
      {
        $option = $this->profileOptions[$match[1]];
        if ($option->exists())
        {
          $option->delete();
        }
      }
      elseif ($key == 'new' && $value)
      {
        $option = new ProfileOption();
        $option->setProfile($this->profile);
        $option->setValue($value);
        $option->setSortOrder($sortOrder++);
        $option->save();
      }
    }
  }

 /**
  * bin and save
  *
  * @param array $taintedValues
  * @return boolean
  */
  public function bindAndSave($taintedValues)
  {
    $this->bind($taintedValues);
    if ($this->isValid())
    {
      $this->save();

      return true;
    }

    return false;
  }
}
