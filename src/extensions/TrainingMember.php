<?php

namespace Letsco\Training;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;

/**
 * TrainingMember
 *
 * @package training
 * @author Johann
 * @since 2021.04.27
 * @extends Member
 */
class TrainingMember extends DataExtension
{
    private static $db = array(
        // 'TrainingId' => 'Varchar',
    );

    public function updateCMSFields(FieldList $fields)
    {
        // $tab = $fields->findOrMakeTab('Root.Training', _t('TrainingAdmin.MENUTITLE', 'Training'));
    }

    public function onBeforeWrite()
    {
        // if (!$this->owner->ID) {
        //     return;
        // }
        // if (!$this->owner->TrainingApiKey) {
        //     $this->owner->TrainingApiKey = TrainingHelper::getApiKey();
        // }
        // if (!$this->owner->TrainingSecretKey) {
        //     $this->owner->StriTrainingSecretKeypeSecretKey = TrainingHelper::getSecretKey();
        // }
    }
}
