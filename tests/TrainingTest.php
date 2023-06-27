<?php

namespace Letsco\Training\Test;

use SilverStripe\Dev\SapphireTest;

class TrainingTest extends SapphireTest
{
    protected static $fixture_file = 'vendor/letsco-co/silverstripe-training/tests/TrainingTest.yml';

    public function test()
    {
        $this->assertEquals(1, intval("1"));

        $session = $this->objFromFixture('Letsco\Training\TrainingSession', 'Test1');
        $this->assertEquals(0, $session->Signatures()->count());

        $_REQUEST['date'] = '2023-07-01 8:30';
        (new \Letsco\Training\TrainingGenerateDailySignatureNotificationTask())->process(false);
        $this->assertEquals(1, $session->Signatures()->count());

        $_REQUEST['date'] = '2023-07-02 8:30';
        (new \Letsco\Training\TrainingGenerateDailySignatureNotificationTask())->process(false);
        $this->assertEquals(1, $session->Signatures()->count());

        $_REQUEST['date'] = '2023-07-02 10:30';
        (new \Letsco\Training\TrainingGenerateDailySignatureNotificationTask())->process(false);
        $this->assertEquals(2, $session->Signatures()->count());
    }
}