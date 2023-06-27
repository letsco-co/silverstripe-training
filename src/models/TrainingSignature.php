<?php

namespace Letsco\Training;

use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Director;
use SilverStripe\Security\Member;
use SilverStripe\Security\RandomGenerator;
use LeKoala\EmailTemplates\Models\EmailTemplate;

/**
 * TrainingSignature
 *
 * @package training
 * @author Johann
 * @since 2021.04.27
 */
class TrainingSignature extends DataObject
{
    private static $table_name = 'TrainingSignature';
    private static $db = [
        'StartDate' => 'Datetime',
        'EndDate'   => 'Datetime',
        'Token'     => 'Varchar(64)',
        'Signed'    => 'Datetime',
        'Signature' => 'Text',
    ];
    private static $has_one = [
        'Member'  => Member::class,
        'Session' => TrainingSession::class,
        'Day'     => TrainingDay::class,
    ];
    private static $summary_fields = array(
    	'Session.Title', 'Member.FirstName', 'Member.Surname', 'StartDate', 'EndDate', 'Signed',
    );
    private static $better_buttons_actions = array(
        'resend',
    );

    public function onBeforeWrite()
    {
    	parent::onBeforeWrite();

    	if (!$this->Signed) {
    		$this->Token = $this->generateUniqueToken();
    	} else {
    		$this->Token = null;
    	}
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        if ($this->Signed) {
            $fields->removeByName(['Token', 'Signature']);
            $fields->push(LiteralField::create('Image', $this->SignatureImage()));
        }

        // Improve TrainingDay creation by adding a complete days table with big save/remove and append easily.

        return $fields;
    }

    // public function getBetterButtonsActions()
    // {
    //     $fields = parent::getBetterButtonsActions();

    //     if (!$this->Signed) {
    //         $fields->push(BetterButtonCustomAction::create('resend', "Renvoyer l'email")->setRedirectType(BetterButtonCustomAction::REFRESH));
    //     }
        
    //     return $fields;
    // }

    /**
     * A BetterButtons custom action that allows the email to be resent
     */
    public function resend()
    {
        if ($res = $this->doNotify()) {
            return 'Sent';
        }
        return 'Could not send email';
    }

    public function doNotify()
    {
        $Member = $this->Member();

        if (class_exists('EmailTemplate')) {
            $template = EmailTemplate::getByCode('training-signature');
            $email = $template->getEmail();
            $email->setTo($Member->Email);
            $email->populateTemplate([
                'Signature' => $this,
                'Member' => $Member,
                'TrainingSession' => $this->Session(),
                'Date' => $this->TranslatedPeriod(),
            ]);
            return $email->send();
        }

        return true;
    }

    /**
     * Generate a new string
     *
     * @return string
     */
    public function generateUniqueToken()
    {
        // return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

		$random = new RandomGenerator();
		do {
			$token = substr($random->randomToken(), 0, 64);
		} while (self::get()->filter('Token', $token)->count() > 0);

		return $token;
    }

    public function Link()
    {
    	return Director::absoluteURL('/training/sign?token='. $this->Token);
    }

    public function SignatureImage()
    {
    	return '<img src="'. $this->Signature .'">';
    }

    public function TranslatedPeriod()
    {
        $date = date('d/m/Y', strtotime($this->StartDate));
        $start = date('H:i', strtotime($this->StartDate));
        $end = date('H:i', strtotime($this->EndDate));
        return _t(__CLASS__.'.TranslatedPeriod', 'le {date} de {start} à {end}', [
            'date' => $date,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function TranslatedSigned()
    {
        $date = date('d/m/Y', strtotime($this->Signed));
        $hour = date('H:i', strtotime($this->Signed));
        return _t(__CLASS__.'.TranslatedSigned', 'le {date} à {hour}', [
            'date' => $date,
            'hour' => $hour,
        ]);
    }

    public function IsMorning()
    {
        return $this->StartDate == $this->Day()->StartDateMorning();
    }

    public function IsAfternoon()
    {
        return $this->StartDate == $this->Day()->StartDateAfternoon();
    }
}
