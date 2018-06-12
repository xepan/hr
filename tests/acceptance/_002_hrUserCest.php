<?php
namespace hr;

use \SuperUser;
use \Codeception\Util\Locator;

class _002_hrUserCest
{
    public function _before(SuperUser $I)
    {
    }

    public function _after(SuperUser $I)
    {
    }
    
    // function testUser(SuperUser $i){
    //     $i->login('management@xavoc.com');
    //     $i->clickMenu('HR->User');
    //     // $i->waitPageLoad();
    //     $i->see('Add User');
    //     $i->click('Add User');
    //     $i->waitPageLoad();
    //     $i->fillAtkField('username','');
    //     $i->click('Add');
    //     $i->waitForText('Username must not be empty',2);
    //     $i->fillAtkField('username','1231');
    //     $i->click('Add');
    //     $i->waitForText('Username must be a valid email address',2);
        
    //     $i->fillAtkField('username','vijay.mali552@gmail.com');
    //     $i->fillAtkField('password','123');
    //     $i->select2Option('scope',['text'=>'SuperUser']);
    //     $i->wait(3);
    //     $i->click('Add');
    // }
}
