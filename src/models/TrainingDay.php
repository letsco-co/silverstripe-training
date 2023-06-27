<?php

namespace Letsco\Training;

use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Director;

/**
 * TrainingDay
 *
 * @package training
 * @author Johann
 * @since 2021.04.27
 */
class TrainingDay extends DataObject
{
    private static $table_name = 'TrainingDay';
    private static $db = [
        'Date'               => 'Date',
        'StartTimeMorning'   => 'Time',
        'EndTimeMorning'     => 'Time',
        'StartTimeAfternoon' => 'Time',
        'EndTimeAfternoon'   => 'Time',
    ];
    private static $has_one = [
        'Session'            => TrainingSession::class,
    ];
    private static $summary_fields = array(
    	'Date', 'StartTimeMorning', 'EndTimeMorning', 'StartTimeAfternoon', 'EndTimeAfternoon',
    );

    public static function getActives($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d H:i:s');
        }
    	$day = date('Y-m-d', strtotime($date));
    	$time = date('H:i:s', strtotime($date));
        
        $list = self::get()->where("
        	Date = '$day' AND
        	(
        		(StartTimeMorning <= '$time' AND '$time' < EndTimeMorning) OR 
        		(StartTimeAfternoon <= '$time' AND '$time' < EndTimeAfternoon)
        	)
        ");
        return $list;
    }

    public function StartDateMorning()
    {
        return $this->Date .' '. $this->StartTimeMorning;
    }

    public function StartDateAfternoon()
    {
        return $this->Date .' '. $this->StartTimeAfternoon;
    }

    public function EndDateMorning()
    {
        return $this->Date .' '. $this->EndTimeMorning;
    }

    public function EndDateAfternoon()
    {
        return $this->Date .' '. $this->EndTimeAfternoon;
    }

    /**
     * In seconds
     */
    public function MorningDuration()
    {
        return strtotime($this->EndDateMorning()) - strtotime($this->StartDateMorning());
    }

    /**
     * In seconds
     */
    public function AfternoonDuration()
    {
        return strtotime($this->EndDateAfternoon()) - strtotime($this->StartDateAfternoon());
    }

    /**
     * In seconds
     */
    public function TotalDuration()
    {
        return $this->MorningDuration() + $this->AfternoonDuration();
    }

    public function TranslatedPeriodMorning()
    {
        if ($this->StartTimeMorning) {
            return $this->TranslatedPeriod($this->StartTimeMorning, $this->EndTimeMorning);
        }
    }

    public function TranslatedPeriodAfternoon()
    {
        if ($this->StartTimeAfternoon) {
            return $this->TranslatedPeriod($this->StartTimeAfternoon, $this->EndTimeAfternoon);
        }
    }

    public function TranslatedPeriod($start, $end)
    {
        $start = substr($start, 0, -3); // date('H:i', strtotime('H:i:s', $start));
        $end = substr($end, 0, -3); // date('H:i', strtotime('H:i:s', $end));
        return _t(__CLASS__.'.TranslatedPeriod', 'de {start} à {end}', [
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function StartDateTime($time)
    {
        if (!$time) {
            $time = date('H:i:s');
        }
    	return $this->Date .' '. ($this->StartTimeAfternoon <= $time ? $this->StartTimeAfternoon : $this->StartTimeMorning);
    }

    public function EndDateTime($time)
    {
        if (!$time) {
            $time = date('H:i:s');
        }
    	return $this->Date .' '. ($time <= $this->EndTimeMorning ? $this->EndTimeMorning : $this->EndTimeAfternoon);
    }

    // public function MorningSignatures()
    // {
    //     return TrainingSignature::get()->filter([
    //         'SessionID' => $this->SessionID,
    //         'StartDate' => $this->StartDateTime($this->StartTimeMorning),
    //         'EndDate'   => $this->EndDateTime($this->EndTimeMorning),
    //     ]);
    // }

    // public function AfternoonSignatures()
    // {
    //     return TrainingSignature::get()->filter([
    //         'SessionID' => $this->SessionID,
    //         'StartDate' => $this->StartDateTime($this->StartTimeAfternoon),
    //         'EndDate'   => $this->EndDateTime($this->EndTimeAfternoon),
    //     ]);
    // }

    /*
    [
        {
            Member: Member,
            MorningSignature: Signature,
            AfternoonSignature: Signature
        },
        ...
    ]
    */
    public function MemberSignatures()
    {
        // $morning = $this->Date .' '. $this->StartTimeMorning;
        // $afternoon = $this->Date .' '. $this->StartTimeAfternoon;

        $Signatures = TrainingSignature::get()
                ->filter([
                    'SessionID' => $this->SessionID,
                    'DayID' => $this->ID,
                ])
                // ->where("StartDate = '$morning' OR StartDate = '$afternoon'") // morning and afternoon
                ->leftJoin('Member', 'TrainingSignature.MemberID = Member.ID')
                ->sort('Member.Surname, Member.FirstName, StartDate');
        SS_Log::Log(__FUNCTION__.' >> '. $Signatures->sql(), SS_Log::INFO);
        return $Signatures;
    }

    public function findOrMakeSignature($Member, $time)
    {
        $StartDate = $this->StartDateTime($time);
        $EndDate = $this->EndDateTime($time);

        $Signature = TrainingSignature::get()->filter([
            'MemberID' => $Member->ID,
            'SessionID' => $this->SessionID,
            'DayID' => $this->ID,
            'StartDate' => $StartDate,
            'EndDate' => $EndDate,
        ])->first();
        if (!$Signature) {
            $Signature = TrainingSignature::create();
            $Signature->MemberID = $Member->ID;
            $Signature->SessionID = $this->SessionID;
            $Signature->DayID = $this->ID;
            $Signature->StartDate = $StartDate;
            $Signature->EndDate = $EndDate;
            $Signature->write();
        }
        return $Signature;
    }

    // public function getCMSFields()
    // {
    //     $fields = parent::getCMSFields();

    //     $SignaturesTab = $fields->findOrMakeTab('Root.Signatures');
    //     if ($SignaturesTab) {
    //         $SignaturesTab->setTitle(singleton('TrainingSignature')->i18n_plural_name());
    //     }

    //     return $fields;
    // }
}
