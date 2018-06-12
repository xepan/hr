<?php
namespace hr;

use \SuperUser;
use \Codeception\Util\Locator;

class _004_hrAffiliateCest
{
    public function _before(SuperUser $I)
    {
    }

    public function _after(SuperUser $I)
    {
    }


    // function testAffiliate(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Affiliate');
    //     // $i->waitPageLoad();
    //     $i->see('Add Affiliate');
    // }

    // function test_add_affiliate(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Affiliate');
    //     $i->see('Add Affiliate');
    //     $i->click('Add Affiliate');
    //     $i->waitPageLoad();
    //     $i->fillAtkField('first_name','vijay');
    //     $i->fillAtkField('last_name','mali');
    //     $i->fillAtkField('organization','Xavoc Technocrats Pvt. Ltd');
    //     $i->fillAtkField('post','Developer');
    //     $i->fillAtkField('address','18/436, Gaytri marg Kanji Ka Hata');
    //     $i->fillAtkField('city','Udaipur');
    //     $i->fillAtkField('pin_code','313001');
    //     $i->click('Save');
    // }

    // function test_add_affiliate_detail(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Affiliate');
    //     $i->see('Add Affiliate');
    //     $i->click(['css'=>'table tbody tr td:nth-child(4) a.pb_edit']);
    //     $i->waitPageLoad();
    //     $i->waitForText('Affiliate Details');
    //     $i->fillAtkField('narration','Test Case Affiliate');
    //     $i->click('Save');
    // }
    
    // function test_add_affiliate_contact_email(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Affiliate');
    //     $i->see('Add Affiliate');
    //     $i->click(['css'=>'table tbody tr td:nth-child(4) a.pb_edit']);
    //     $i->waitPageLoad();
    //     $i->waitForText('Affiliate Details');
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

    // function test_add_affiliate_contact_number(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Affiliate');
    //     $i->see('Add Affiliate');
    //     $i->click(['css'=>'table tbody tr td:nth-child(4) a.pb_edit']);
    //     $i->waitPageLoad();
    //     $i->waitForText('Affiliate Details');
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

    // function test_add_affiliate_contact_IM(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Affiliate');
    //     $i->see('Add Affiliate');
    //     $i->click(['css'=>'table tbody tr td:nth-child(4) a.pb_edit']);
    //     $i->waitPageLoad();
    //     $i->waitForText('Affiliate Details');
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


}
