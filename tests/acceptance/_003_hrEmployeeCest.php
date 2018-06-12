<?php
namespace hr;

use \SuperUser;
use \Codeception\Util\Locator;

class _003_hrEmployeeCest
{
    public function _before(SuperUser $I)
    {
    }

    public function _after(SuperUser $I)
    {
    }

    
    // function testEmployee(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Employee');
    //     $i->see('Add Employee');
    //     $i->click(['css'=>'table tbody tr td:nth-child(1) a.do-view-employee']);
    //     $i->click('Official');
    //     $i->waitForText('Offer Date');
    //     $i->click('Activity');
    //     $i->wait(2);
    //     $i->click('Documents');
    //     $i->waitForText('No document found');
    //     // $i->click(['css'=>' button.ui-dialog-titlebar-close']);
    //     $i->closeDialog();
    //     $i->dontSee('Employee Details');
    // }
    // function test_add_employee(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Employee');
    //     $i->see('Add Employee');
    //     $i->click('Add Employee');
    //     $i->waitPageLoad();
    //     $i->fillAtkField('first_name','vijay');
    //     $i->fillAtkField('last_name','mali');
    //     $i->fillAtkField('organization','Xavoc Technocrats Pvt. Ltd');
    //     $i->fillAtkField('address','18/436, Gaytri marg Kanji Ka Hata');
    //     $i->wait(10);
    //     // $i->selectOption('country', 'India');
    //     // $i->selectOption('state', ['Rajasthan']);
    //     $i->fillAtkField('city','Udaipur');
    //     $i->fillAtkField('pin_code','313001');
    //     $i->click('Save');
    // }

    // // function test_add_employee_details(SuperUser $i){
    // //     $i->login('management@xavoc.com');
    // //     $i->clickMenu('HR->Employee');
    // //     $i->click(['css'=>'table tbody tr:nth-child(2) td:nth-child(5) a']);
    // //     $i->waitPageLoad();
    // //     $i->select2Option("department_id",['text'=>'Company']);
    // //     // $i->select2Option("post_id",['text'=>'management']);
    // //     $i->save();
    // // }
    // function test_add_employee_contact_email(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Employee');
    //     $i->see('Add Employee');
    //     $i->click(['css'=>'table tbody tr:nth-child(2) td:nth-child(5) a.pb_edit']);
    //     $i->waitPageLoad();
    //     $i->waitForText('Employee Details');
    //     $i->click('Add Contact Email');
    //     $i->waitForText('Adding new Contact Email');
    //     $i->select2Option('head','');
    //     $i->click('Add');
    //     $i->waitForText('Head must not be empty');
    //     $i->select2Option('head',['text'=>'Official']);
    //     $i->fillAtkField('value','');
    //     $i->click('Add');
    //     $i->waitForText('Value must not be empty');
    //     $i->fillAtkField('value','vijay.mali552@gmail.com');
    //     $i->unCheckCheckBox('is_active');
    //     $i->unCheckCheckBox('is_valid');
    //     $i->click('Add');
    //     $i->waitForText('vijay.mali552@gmail.com');
    // }

    // function test_add_employee_contact_number(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Employee');
    //     $i->see('Add Employee');
    //     $i->click(['css'=>'table tbody tr:nth-child(2) td:nth-child(5) a.pb_edit']);
    //     $i->waitPageLoad();
    //     $i->waitForText('Employee Details');
    //     $i->click('Add Contact Phone');
    //     $i->waitForText('Adding new Contact Phone');
    //     $i->select2Option('head','');
    //     $i->click('Add');
    //     $i->waitForText('Head must not be empty');
    //     $i->select2Option('head',['text'=>'Mobile']);
    //     $i->fillAtkField('value','9784954128');
    //     // $i->click('is Active');
    //     // $i->click('is Valid');
    //     $i->click('Add');
    //     $i->waitForText('9784954128');
    // }

    // function test_add_employee_contact_IM(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Employee');
    //     $i->see('Add Employee');
    //     $i->click(['css'=>'table tbody tr:nth-child(2) td:nth-child(5) a.pb_edit']);
    //     $i->waitPageLoad();
    //     $i->waitForText('Employee Details');
    //     $i->click('Add Contact IM');
    //     $i->waitForText('Adding new Contact IM');
    //     $i->select2Option('head','');
    //     $i->click('Add');
    //     $i->waitForText('Head must not be empty');
    //     $i->select2Option('head',['text'=>'WhatsApp']);
    //     $i->fillAtkField('value','9784954128');
    //     // $i->click('is Active');
    //     // $i->click('is Valid');
    //     $i->click('Add');
    //     $i->waitForText('9784954128');
    // }
    
    // function testEmployeeMovement(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Employee Movement');
    //     $i->waitForText('Employee Movement');
        
    // }
}
