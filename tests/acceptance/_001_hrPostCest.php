<?php
namespace hr;

use \SuperUser;
use \Codeception\Util\Locator;

class _001_hrPostCest
{
    public function _before(SuperUser $I)
    {
    }

    public function _after(SuperUser $I)
    {
    }

    // function testPost(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Post');
    //     $i->wait(2);
    //     $i->waitForText('CEO');
    //     $i->click(['css'=>'table tbody tr td:nth-child(4) a.emails-accesible']);
    //     // $i->acceptPopup();
    //     $i->see('Permitted Emails');
    //     $i->click(['css'=>' button.editable-cancel']);
    //     $i->wait(2);
    //     $i->click(['css'=>'table tbody tr td:nth-child(5) a.do-view-post-employees']);
    //     $i->see('Post Employees');
    //     $i->wait(2);
    //     $i->closeDialog();
    //     $i->dontSee('Post Employees');
    //     // $i->click(['css'=>' button.ui-dialog-titlebar-close']);
    //     // $i->click(['css'=>'table tbody tr td:nth-child(1) a.do-view-employee']);
    //     // $i->see('Employee Details');
    //     // $i->wait(2);
    //     // $i->click(['css'=>' button.ui-dialog-titlebar-close']);
    //     // $i->see('Post Employees');
    // }

    // function test_add_post(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->Post');
    //     $i->waitForText('Add Post');
    //     $i->click('Add Post');

    //     $i->fillAtkField('name','management');
    //     $i->click('Add');
    //     $i->waitForElement('.field-error-text');
    //     $i->see('Department_id must not be empty');
    //     $i->select2Option("department_id",['text'=>'Company']);
    //     $i->click('Add');
    //     $i->see('management');
    //     $i->clickActionForRow(2,'Deactivate');
    //     $i->dontSee('management');
    //     $i->click('InActive');
    //     $i->waitForPageLoad();
    //     $i->see('management');
    //     $i->clickActionForRow(1,'Activate');
    //     $i->see('No matching records found');
    // }
}
