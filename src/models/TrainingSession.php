<?php

namespace Letsco\Training;

use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Director;
use SilverStripe\Security\Member;

/**
 * TrainingSession
 *
 * @package training
 * @author Johann
 * @since 2021.04.27
 */
class TrainingSession extends DataObject
{
    private static $table_name = 'TrainingSession';
    private static $db = [
        'Title'       => 'Varchar(255)',
        'Description' => 'HTMLText',
    ];
    private static $has_many = [
        'Days'        => TrainingDay::class,
    ];
    private static $many_many = [
        'Members'     => Member::class,
    ];

    public function Signatures()
    {
        return TrainingSignature::get()->filter('SessionID', $this->ID);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Improve TrainingDay creation by adding a complete days table with big save/remove and append easily.

        $SignaturesTab = $fields->findOrMakeTab('Root.Signatures');
        $SignaturesTab->setTitle(singleton('TrainingSignature')->i18n_plural_name());

        $config = new GridFieldConfig_RecordViewer();
        $config->addComponent(new GridFieldExportAllButton());
        $config->addComponent(new TrainingGridFieldPrintAsPdfButton($this));
        $SignaturesTab->push(new GridField('Signatures', '', $this->Signatures(), $config));

        return $fields;
    }

    public function summaryFields()
    {
        $fields = array();

        $fields['Title'] = _t(__CLASS__ .'.Title', 'Titre');
        $fields['Members.Count'] = _t(__CLASS__ .'.Members', 'Participants');
        $fields['DaysSum'] = _t(__CLASS__ .'.Days', 'Total');
        $fields['SignaturesSum'] = _t(__CLASS__ .'.Signatures', 'Signatures');

        return $fields;
    }

    public function DaysSum()
    {
        $seconds = 0;

        foreach ($this->Days() as $day) {
            $seconds += $day->TotalDuration() * $this->Members()->count();
        }

        $hours = intval($seconds / 3600);
        $minutes = ($seconds - ($hours*3600)) / 60;
        return $hours .'h'. ($minutes ? ' '. $minutes .'m' : '');
    }

    public function SignaturesSum()
    {
        $Signatures = $this->Signatures();
        return $Signatures->where('Signed IS NOT NULL')->count() .' / '. $Signatures->count();
    }
}
