<?php

// use Dompdf\Dompdf;

/**
 * Adds an "Print" button to the bottom or top of a GridField.
 *
 * @package training
 * @author Johann
 * @since 2021.04.27
 * @extends GridFieldPrintButton
 */
// class TrainingGridFieldPrintAsPdfButton extends GridFieldPrintButton
// {
//     protected $TrainingSession;

//     /**
//      * @param string $targetFragment The HTML fragment to write the button into
//      * @param array $printColumns The columns to include in the print view
//      */
//     public function __construct($TrainingSession, $targetFragment = "after", $printColumns = null) {
//         parent::__construct($targetFragment, $printColumns);
//         $this->TrainingSession = $TrainingSession;
//     }

//     /**
//      * Handle the print, for both the action button and the URL
//      */
//     public function handlePrint($gridField, $request = null)
//     {
//         $dompdf = new Dompdf(array('enable_remote' => true)); // Enable remote to embbed images.
//         // $dompdf->setPaper('A4', 'landscape');
//         $dompdf->loadHtml($this->TrainingSession->renderWith('TrainingSessionSignaturesPrint'));
//         $dompdf->render();
//         $dompdf->stream('TrainingSignature-'. $this->TrainingSession->Title .'-'. Date("Ymd-Hi") .'.pdf');
//         exit();
//     }
// }