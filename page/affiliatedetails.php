<?php

namespace xepan\hr;

class page_affiliatedetails extends \xepan\base\Page {
	public $title ='Affiliate Details';
	public $breadcrumb=['Home'=>'index','Affiliate'=>'xepan_hr_affiliate','Details'=>'#'];


	function init(){
		parent::init();

		$action = $this->api->stickyGET('action')?:'view';
		$affiliate= $this->add('xepan\hr\Model_Affiliate')->tryLoadBy('id',$this->api->stickyGET('contact_id'));
		
		if($action=="add"){

			$this->template->tryDel('details');
			$base_validator = $this->add('xepan\base\Controller_Validator');
			
			$form = $this->add('Form',['validator'=>$base_validator],'contact_view_full_width');
			$form->setLayout(['page/affiliate-compact']);
			$form->setModel($affiliate,['first_name','last_name','address','city','country_id','state_id','pin_code','organization','post','website','narration']);
			$form->addField('line','email_1')->validate('email');
			$form->addField('line','email_2');
			$form->addField('line','email_3');
			$form->addField('line','email_4');

			$country_field =  $form->getElement('country_id');
			$state_field = $form->getElement('state_id');
			$state_field->dependsOn($country_field);
			// if($cntry_id = $this->app->stickyGET('country_id')){			
			// 	$state_field->getModel()->addCondition('country_id',$cntry_id);
			// }
			// $country_field->js('change',$state_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$state_field->name]),'country_id'=>$country_field->js()->val()]));

			$form->addField('line','contact_no_1');
			$form->addField('line','contact_no_2');
			$form->addField('line','contact_no_3');
			$form->addField('line','contact_no_4');
			$form->addField('Checkbox','want_to_add_next_affiliate')->set(true);

			$affiliate->addOtherInfoToForm($form);

			$form->addSubmit('Add')->addClass('btn btn-primary');
			if($form->isSubmitted()){			
				try{
					$this->api->db->beginTransaction();
					$form->save();
					$new_affiliate_model = $form->getModel();

					if($form['email_1']){
						$new_affiliate_model->checkEmail($form['email_1'],null,'email_1');

						$email = $this->add('xepan\base\Model_Contact_Email');
						$email['contact_id'] = $new_affiliate_model->id;
						$email['head'] = "Official";
						$email['value'] = trim($form['email_1']);
						$email->save();
					}

					if($form['email_2']){
						$new_affiliate_model->checkEmail($form['email_2'],null,'email_2');

						$email = $this->add('xepan\base\Model_Contact_Email');
						$email['contact_id'] = $new_affiliate_model->id;
						$email['head'] = "Official";
						$email['value'] = trim($form['email_2']);
						$email->save();
					}

					if($form['email_3']){
						$new_affiliate_model->checkEmail($form['email_3'],null,'email_3');

						$email = $this->add('xepan\base\Model_Contact_Email');
						$email['contact_id'] = $new_affiliate_model->id;
						$email['head'] = "Personal";
						$email['value'] = trim($form['email_3']);
						$email->save();
					}
					if($form['email_4']){
						$new_affiliate_model->checkEmail($form['email_4'],null,'email_4');

						$email = $this->add('xepan\base\Model_Contact_Email');
						$email['contact_id'] = $new_affiliate_model->id;
						$email['head'] = "Personal";
						$email['value'] = trim($form['email_4']);
						$email->save();
					}

					// Contact Form
					if($form['contact_no_1']){
						$new_affiliate_model->checkPhone($form['contact_no_1'],null,'contact_no_1');

						$phone = $this->add('xepan\base\Model_Contact_Phone');
						$phone['contact_id'] = $new_affiliate_model->id;
						$phone['head'] = "Official";
						$phone['value'] = $form['contact_no_1'];
						$phone->save();
					}

					if($form['contact_no_2']){
						$new_affiliate_model->checkPhone($form['contact_no_2'],null,'contact_no_2');

						$phone = $this->add('xepan\base\Model_Contact_Phone');
						$phone['contact_id'] = $new_affiliate_model->id;
						$phone['head'] = "Official";
						$phone['value'] = $form['contact_no_2'];
						$phone->save();
					}

					if($form['contact_no_3']){
						$new_affiliate_model->checkPhone($form['contact_no_3'],null,'contact_no_3');

						$phone = $this->add('xepan\base\Model_Contact_Phone');
						$phone['contact_id'] = $new_affiliate_model->id;
						$phone['head'] = "Personal";
						$phone['value'] = $form['contact_no_3'];
						$phone->save();
					}
					if($form['contact_no_4']){
						$new_affiliate_model->checkPhone($form['contact_no_4'],null,'contact_no_4');

						$phone = $this->add('xepan\base\Model_Contact_Phone');
						$phone['contact_id'] = $new_affiliate_model->id;
						$phone['head'] = "Personal";
						$phone['value'] = $form['contact_no_4'];
						$phone->save();
					}

					// add contact other info
					$contact_other_info_config_m = $this->add('xepan\base\Model_Config_ContactOtherInfo');
					$contact_other_info_config_m->addCondition('for','Affiliate');
					foreach($contact_other_info_config_m->config_data as $of) {
						if($of['for'] != "Affiliate" ) continue;

						if(!$of['name']) continue;
						$field_name = $this->app->normalizeName($of['name']);

						$existing = $this->add('xepan\base\Model_Contact_Other')
							->addCondition('contact_id',$new_affiliate_model->id)
							->addCondition('head',$of['name'])
							->tryLoadAny();
						$existing['value'] = $form[$field_name];
						$existing->save();
					}
					$this->api->db->commit();
				}catch(\Exception_StopInit $e){

		        }catch(\Exception $e){
		            $this->api->db->rollback();
		            throw $e;
		        }	

			        if($form['want_to_add_next_affiliate']){
			        	$form->js(null,$form->js()->reload())->univ()->successMessage('Affiliate Created Successfully')->execute();
			        }
					
					$form->js(null,$form->js()->univ()->successMessage('Affiliate Created Successfully'))->univ()->redirect($this->app->url(null,['action'=>"edit",'contact_id'=>$new_affiliate_model->id]))->execute();
				}

			// }else{
			// 	$affiliate_view = $this->add('xepan\base\View_Contact',['acl'=>'xepan\hr\Model_Employee','view_document_class'=>'xepan\hr\View_Document'],'contact_view');
			// 	$affiliate_view->setModel($affiliate);
			// }

			// $affiliate_view = $this->add('xepan\base\View_Contact',['acl'=>'xepan\hr\Model_Employee','view_document_class'=>'xepan\hr\View_Document','page_reload'=>($action=='add')],'contact_view_full_width');
			// $affiliate_view->document_view->effective_template->del('im_and_events_andrelation');
			// $affiliate_view->document_view->effective_template->del('email_and_phone');
			// $affiliate_view->document_view->effective_template->del('avatar_wrapper');
			// $affiliate_view->document_view->effective_template->del('contact_since_wrapper');
			// $affiliate_view->document_view->effective_template->del('send_email_sms_wrapper');
			// $affiliate_view->document_view->effective_template->del('online_status_wrapper');
			// $affiliate_view->document_view->effective_template->del('contact_type_wrapper');
			// $this->template->del('details');
			// $affiliate_view->setStyle(['width'=>'50%','margin'=>'auto']);
			$this->template->del('other_details');
		}else{
			$this->template->del('contact_view_full_width');
			$affiliate_view = $this->add('xepan\base\View_Contact',['acl'=>'xepan\hr\Model_Employee','view_document_class'=>'xepan\hr\View_Document'],'contact_view');
			$affiliate_view->setModel($affiliate);
		}	

		// $affiliate_view->setModel($affiliate);

		$detail = $this->add('xepan\hr\View_Document',['action'=> $action,'id_field_on_reload'=>'contact_id'],'details',['view/affiliate/details']);
		$detail->setModel($affiliate,['narration'],['narration']);

	}

	function defaultTemplate(){
		return ['page/affiliateprofile'];
	}

	function checkPhoneNo($phone,$phone_value,$contact,$form){

		 $contact = $this->add('xepan\base\Model_Contact');
        
        if($contact->id)
	        $contact->load($contact->id);

		$contactconfig_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'contact_no_duplcation_allowed'=>'DropDown'
							],
					'config_key'=>'contact_no_duplication_allowed_settings',
					'application'=>'base'
			]);
		$contactconfig_m->tryLoadAny();	

		if($contactconfig_m['contact_no_duplcation_allowed'] != 'duplication_allowed'){
	        $contactphone_m = $this->add('xepan\base\Model_Contact_Phone');
	        $contactphone_m->addCondition('id','<>',$phone->id);
	        $contactphone_m->addCondition('value',$phone_value);
			
			if($contactconfig_m['contact_no_duplcation_allowed'] == 'no_duplication_allowed_for_same_contact_type'){
				$contactphone_m->addCondition('contact_type',$contact['contact_type']);
		        $contactphone_m->tryLoadAny();
		 	}

	        $contactphone_m->tryLoadAny();
	        
	        if($contactphone_m->loaded())
	        	for ($i=1; $i <=4 ; $i++){ 
	        		if($phone_value == $form['contact_no_'.$i])
			        	$form->displayError('contact_no_'.$i,'Contact No. Already Used');
	        	}
		}	
    }

    function checkEmail($email,$email_value,$contact,$form){

    	$contact = $this->add('xepan\base\Model_Contact');
        
        if($contact->id)
	        $contact->load($contact->id);

		$emailconfig_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'email_duplication_allowed'=>'DropDown'
							],
					'config_key'=>'Email_Duplication_Allowed_Settings',
					'application'=>'base'
			]);
		$emailconfig_m->tryLoadAny();

		if($emailconfig_m['email_duplication_allowed'] != 'duplication_allowed'){
	        $email_m = $this->add('xepan\base\Model_Contact_Email');
	        $email_m->addCondition('id','<>',$email->id);
	        $email_m->addCondition('value',$email_value);
			
			if($emailconfig_m['email_duplication_allowed'] == 'no_duplication_allowed_for_same_contact_type'){
				$email_m->addCondition('contact_type',$contact['contact_type']);
			}
	        
	        $email_m->tryLoadAny();
	        
	        if($email_m->loaded())
	        	for ($i=1; $i <=4 ; $i++){ 
	        		if($email_value == $form['email_'.$i])
			        	$form->displayError('email_'.$i,'Email Already Used');
	        	}
		}	
    }

}
