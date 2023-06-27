<?php

namespace Letsco\Training;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;

/**
 * TrainingSiteConfig
 *
 * @package training
 * @author Johann
 * @since 2021.04.27
 * @extends SiteConfig
 */
class TrainingSiteConfig extends DataExtension
{
    private static $db = array(
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
