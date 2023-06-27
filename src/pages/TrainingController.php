<?php

namespace Letsco\Training;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Session;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\View\Requirements;
use Letsco\Utils\CanvasField;

/**
 * TrainingController
 *
 * @package training
 * @author Johann
 * @since 2021.04.27
 */
class TrainingController extends Controller
{
	private static $allowed_actions = array(
		'sign',
		'SignForm',
		'doSign',
    );

    public function index()
    {
    	return $this->httpError(404);
    }

    /**
     * Display a signature form for a specific training/user/date
     */
    public function SignForm()
    {
    	$Signature = $this->Signature();

        $fields = new FieldList(
        	HiddenField::create('token', '', $Signature->Token),
        	HiddenField::create('Signature', '', ''),
            CanvasField::create('SignaturePad', '', '')->setAttribute('width', '480')->setDescription("Merci de signer dans ce cadre")
        );

        $actions = new FieldList(
            FormAction::create('doSign', _t(__CLASS__ .'.doSign', 'Signer'))
        );

        $required = new RequiredFields();
        $required->addRequiredField('Signature');

        Requirements::customCss('
        #Form_SignForm_SignaturePad {
            box-sizing: border-box;
	        background-color: #fff;
	        border: 1px solid #ccc;
	        border-radius: 6px;
	        height: 250px;
        }
        #Form_SignForm_action_doSign {
            background: var(--secondary-color);
            width: 100%;
            margin: 0;
            height: 40px;
            font-size: 20px;
        }
        .description.warning {
            color: red;
            font-size: 200%;
        }');

        Requirements::javascript('mlbc/javascript/signature_pad/signature_pad.min.js');
        Requirements::customScript("
            (function($) {
                $(function () {
	                let canvas = document.getElementById('Form_SignForm_SignaturePad');
	                let signaturePad = new SignaturePad(canvas);
                    let signature = $('#Form_SignForm_Signature');
	                
	                $('.clear-signature').on('click', function(){
	                    signaturePad.clear();
	                });

	                $('#Form_SignForm').on('submit', function(e) {
	        			e.preventDefault();
	        			var form = this;

	        			let dataURL = canvas.toDataURL();
	        			console.log('canvas as image', dataURL);
	        			signature.val(dataURL);

                        console.log(signature.val());
                        if (signaturePad.isEmpty()) {
                            $('#Form_SignForm_SignaturePad_Holder .description').addClass('warning');
                        } else {
                            form.submit();
                        }
	    			});

                    // @see https://github.com/szimek/signature_pad#handling-high-dpi-screens
                    function resizeCanvas() {
                        var ratio =  Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        canvas.getContext('2d').scale(ratio, ratio);
                        signaturePad.clear(); // otherwise isEmpty() might return incorrect value
                    }

                    window.addEventListener('resize', resizeCanvas);
                    resizeCanvas();
                });
            })(jQuery);
        ");

        $form = new Form($this, 'SignForm', $fields, $actions, $required);
        $form->setFormAction('/training/doSign');

        return $form->forTemplate();
    }

    public function doSign($data)
    {
    	$Signature = $this->Signature();
    	$Signature->Signed = date('Y-m-d H:i:s');
    	$Signature->Signature = $data['Signature'];
    	$Signature->write();

    	Session::set('current_training_signature', $Signature->ID);
    	return $this->redirectBack();
    }

    public function Signature()
    {
    	$token = $this->getRequest()->requestVar('token');
    	$Signature = TrainingSignature::get()->filter('Token', $token)->first();
    	if (!$Signature) {
    		$Signature = TrainingSignature::get()->byID(Session::get('current_training_signature'));
    	}
    	if (!$Signature) {
    		// ERR
    	}
    	return $Signature;
    }
}
