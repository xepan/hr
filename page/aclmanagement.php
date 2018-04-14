<?php

/**
* description: AclManagement page is responsible to define ACL for Post and Documents
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class page_aclmanagement extends \xepan\base\Page {
	public $title='Access Control Management';
	public $widget_list = [];
	public $entity_list = [];
	function init(){
		parent::init();
		$this->app->hook('entity_collection',[&$this->entity_list]);

		foreach ($this->entity_list as $key => $entity) {
			
			if(!array_key_exists("model", $entity))
				unset($this->entity_list[$key]);
		// 	$this->array_list[] = ['type'=>$entity['caption']."[".$entity['model']." ]"];
		}
			// echo "<pre>";
			// print_r($this->entity_list);
			// echo "</pre>";

		if($this->app->ACLModel === "none")
			$this->manageAllowACL();
		if($this->app->ACLModel === "Departmental")
			$this->manageDepartmentalACL();
		if($this->app->ACLModel === "Documentbase"){
			$this->add('View')->addClass('alert alert-danger')->set('ACL is defined as Documentbase please go to each document with superuser rights and hit "ACL" button at the top');
			return;
			$this->manageDocumentbaseACL();
		}

	}
	function manageAllowACL(){
		$this->add('H1')->set('ACL Mode Granted Level');
	}

	function manageDepartmentalACL(){
		$this->add('H1')->set('ACL Mode Departmental Level ');
		
		$post_id = $this->api->stickyGET('post_id');
		$emp_id = $this->api->stickyGET('employee_id');
		
		//form 1
		$f = $this->add('Form');
		$post_field = $f->addField('xepan\base\DropDown','post');
		$post_field->setEmptyText('Please Select Post');
		$post_field->setModel('xepan\hr\Post');
		
		$emp_field = $f->addField('xepan\base\DropDown','employee');
		$emp_field->setEmptyText('Please Select Employee');
		$emp_field->setModel('xepan\hr\Employee');
		

		$f->addSubmit('Go');

		$install_app = $this->add('xepan\base\Model_Epan_InstalledApplication')->addCondition('is_active',true);
		
		// form 2
		$form = $this->add('Form');
		if($_GET['post_id'] OR $_GET['employee_id']){
			$app_permission = $this->getInstalledAppPermission($_GET['post_id'],$_GET['employee_id']);
			
			foreach ($install_app as  $app) {
				$app_field = $form->addField('Checkbox',$this->app->normalizeName($app['application_namespace']));
				if(in_array($app->id, $app_permission)){
					$app_field->set(true);
				}
			}
			$form->addSubmit('Update');
		}


		//second form submit
		if($form->isSubmitted()){
			foreach ($install_app as  $app) {
				// echo $app['name'];
				if($form[$this->app->normalizeName($app['application_namespace'])]){
					$emp_dept_asso_m = $this->add('xepan\hr\Model_EmployeeDepartmentalAclAssociation');
					$emp_dept_asso_m->addCondition('employee_id', $emp_id);
					$emp_dept_asso_m->addCondition('post_id', $post_id);
					$emp_dept_asso_m->addCondition('installed_app_id', $app['application_id']);
					$emp_dept_asso_m->tryLoadAny();
					if(!$emp_dept_asso_m->loaded()){
						$emp_dept_asso_m->save();
					}
				}

				if(!$form[$this->app->normalizeName($app['application_namespace'])]){
					$del_emp_dept_asso_m = $this->add('xepan\hr\Model_EmployeeDepartmentalAclAssociation');
					$del_emp_dept_asso_m->addCondition('employee_id', $emp_id);
					$del_emp_dept_asso_m->addCondition('post_id', $post_id);
					$del_emp_dept_asso_m->addCondition('installed_app_id', $app['application_id']);
					$del_emp_dept_asso_m->tryLoadAny();
					if($del_emp_dept_asso_m->loaded()){
						$del_emp_dept_asso_m->delete();
					}
				}
			}

			$form->js()->reload()->execute();
		}


		// first form submit
		if($f->isSubmitted()){
			$f->js(null,$form->js()->reload(['employee_id'=>$f['employee']?:0,'post_id'=>$f['post']?:0]))->execute();
		}

	}

	function manageDocumentbaseACL(){

		// if(!$this->api->auth->model->isSuperUser()){
		// 	$this->add('View_Error')->set('Sorry, you are not permitted to handle acl, Ask respective authority');
		// 	return;
		// }

		$post = $this->api->stickyGET('post_id');
		$ns = $this->api->stickyGET('namespace');
		$dt = $this->api->stickyGET('type');
		
		// $acl_m = $this->add('xepan\hr\Model_ACL');
		// $acl_m->_dsql()->group('name');

		// $acl_m->add('xepan\hr\Controller_ACL');

		$post_m = $this->add('xepan\hr\Model_Post');
		$post_m->addExpression('post_with_department')->set($post_m->dsql()->expr('CONCAT([0]," : ",[1])',[$post_m->getElement('department'),$post_m->getElement('name')]));
		$post_m->title_field = 'post_with_department';

		$form = $this->add('Form',null,null,['form/empty']);
		$form->setLayout('form/aclpost');
		$array_list=[];
		foreach ($this->entity_list as  $a) {
			$string_count = strpos($a['model'], '\Model_');
			$model_namespace = substr($a['model'],0,$string_count);
			$str = $a['caption'].'['.$model_namespace.']';
			$array_list[$str] = $str;
		}

		
		$form->addField('DropDown','post')->addClass('form-control')->setModel($post_m)->set($post);
		$form->addField('DropDown','type')
									->addClass('form-control')
									// ->setModel($acl_m);
									->setValueList($array_list);
									;
		$form->addSubmit('Go')->addClass('btn btn-success');

		$af = $this->add('Form');							

		if($dt){
			$is_config= false;
			try{
				$m = $this->add($ns.'\\Model_'.$dt);
			}catch(\Exception $e){
				try{
					$m = $this->add($ns.'\\'.$dt);
				}catch(\Exception $e1){
					$m = $this->add('xepan\base\Model_ConfigJsonModel',['fields'=>[1],'config_key'=>$dt]);
					$is_config = true;
				}
			}

			$existing_acl = $this->add('xepan\hr\Model_ACL')
								->addCondition('post_id',$post)
								->addCondition('namespace',$ns)
								->addCondition('type',$dt)
								->tryLoadAny();

			if(!$existing_acl->loaded())
				$existing_acl->save();

			if(!$is_config){
				$af->addField('Checkbox','allow_add');
				$af->getElement('allow_add')->set($existing_acl['allow_add']);
			}
			

			$value_list=['Self Only'=>'Self Only','All'=>'All','None'=>'None'];
			
			if(isset($m->assigable_by_field) && $m->assigable_by_field !=false)
				$value_list['Assigned To']='Assigned To Employee';

			if($is_config){
				unset($value_list['Self Only']);
			}

			foreach ($m->actions as $status => $actions) {
				$status = $status=='*'?'All':$status;	
				$greeting_card = $af->add('View', null, null, ['view/acllist1']);
				foreach ($actions as $action) {
					$greeting_card->template->set('action',$status);					
					// $greeting_card->addField('DropDown',$status.'_'.$action,$action)
					$tf = $greeting_card->addField('xepan\base\RadioButton',$status.'_'.$action,$action)
						->setValueList($value_list)
						// ->setEmptyText('Please select ACL')
						->validate('required?Acl must be provided or will work based on global permisible setting')
						->addClass('form-control xepan-custom-radio-btn-field')
						->set($existing_acl['action_allowed'][$status][$action]);
					;

					$tf->template->set('row_class','xepan-radio-btn-form-field');
				}
			}

			$af->addSubmit('Update')->addClass('btn btn-success xepan-margin-top-small');
			$af->template->set('buttons_class','xepan-radio-btn-form-field-clear');
		}

		$af->onSubmit(function($f)use($post,$ns,$dt){
			$is_config = false;
			try{
				$m = $this->add($ns.'\\Model_'.$dt);
			}catch(\Exception $e){
				try{
					$m = $this->add($ns.'\\'.$dt);
				}catch(\Exception $e1){
					$m = $this->add('xepan\base\Model_ConfigJsonModel',['fields'=>[1],'config_key'=>$dt]);
					$is_config = true;
				}
			}
			$acl_array=[];
			foreach ($m->actions as $status => $actions) {
				$status = $status=='*'?'All':$status;			
				foreach ($actions as $action) {
					$acl_array[$status][$action] = $f[$this->api->normalizeName($status.'_'.$action)];
				}
			}

			$class = new \ReflectionClass($m);
			$acl_m = $this->add('xepan\hr\Model_ACL')
					->addCondition('namespace',isset($ns)? $ns:$class->getNamespaceName());
			
			if($m['type']=='Contact' || $m['type']=='Document')
				$m['type'] = str_replace("Model_", '', $class->getShortName());

			$acl_m->addCondition('type',$m['type']?:$m->acl_type);
			$acl_m->addCondition('post_id',$post)
					;
			$acl_m->tryLoadAny();
			$acl_m['action_allowed'] = json_encode($acl_array);
			$acl_m['allow_add'] = $f['allow_add']?:false;
			$acl_m->save();
			return "Done";
		});

		

		$form->onSubmit(function($f)use($af){

			$type = explode("[", $f['type']);
			$ns	=trim($type[1],']');
			
			$acl_m = $this->add('xepan\hr\Model_ACL')
					->addCondition('type',$type[0])
					->addCondition('namespace',$ns)
					->tryLoadAny()
					;												
			return $af->js()->reload(['post_id'=>$f['post'],'namespace'=>$acl_m['namespace'],'type'=> $acl_m['type']]);
		});
		
	}

	function getInstalledAppPermission($post_id=null,$emp_id=null){
		$allow_app_m = $this->add('xepan\hr\Model_EmployeeDepartmentalAclAssociation');
			if($_GET['employee_id']){
				$allow_app_m->addCondition('employee_id',$emp_id);
				$allow_app_m->tryLoadAny();
				if(!$allow_app_m->loaded()){
					$emp_model = $this->add('xepan\hr\Model_Employee')->load($emp_id);
					$emp_post_id =  $emp_model['post_id'];

					$allow_app_m = $this->add('xepan\hr\Model_EmployeeDepartmentalAclAssociation');
					$allow_app_m->addCondition('post_id',$emp_post_id);
						// if loaded the apply this condiiton
						// if not 
							// get employee post id 
							// check condition
				}
			}elseif($_GET['post_id']){
				$allow_app_m->addCondition('post_id',$post_id);
			}
			
			$associated_app = $allow_app_m->_dsql()->del('fields')->field('installed_app_id')->getAll();

			$app_permission = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($associated_app)),false);

		return $app_permission;
	}

	function defaultTemplate(){
		return ['page/aclmanagement'];
	}

}
