<?php

namespace Letsco\Training;

use SilverStripe\Dev\BuildTask;

/**
 * TrainingGenerateDailySignatureNotificationTask
 * Automatically generate ...
 *
 * @package training
 * @author Johann
 * @since 2021.04.27
 */
class TrainingGenerateDailySignatureNotificationTask extends BuildTask /*implements CronTask*/
{
    protected $title = "[training] Generate ...";
    protected $description = "Automatically generate ...";

    private $recap = "";

    public function run($request)
    {
        $this->process(false);
    }

    /**
     * Define schedule. Not used currently, throw it manually instead.
     */
    // public function getSchedule()
    // {
    //     return SimpleJobsSchedules::EVERY_HOUR;
    // }

    /**
     * Process task, send signature notification to every member of active training sessions.
     */
    public function process($dry = true)
    {
        // HTTP::set_cache_age(0);
        // increase_time_limit_to(); // This can be a time consuming task

        $date = date('Y-m-d H:i:s', strtotime(!empty($_REQUEST['date']) ? $_REQUEST['date'] : 'now'));
        $time = date('H:i:s', strtotime($date));

        foreach (TrainingDay::getActives($date) as $day) {
            $Session = $day->Session();
            foreach ($Session->Members() as $Member) {
                $Signature = $day->findOrMakeSignature($Member, $time);
                $res = $Signature->doNotify();
            }
        }
    }

    private function log($msg)
    {
        $this->recap .= "$msg<br/>";
        DB::alteration_message("$msg<br/>", "created");
        if (!empty($msg)) SS_Log::Log(__CLASS__ ." $msg", SS_Log::INFO);
    }
}
