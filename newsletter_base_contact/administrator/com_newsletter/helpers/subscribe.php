<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

class NewsletterHelperSubscribe {

    function _itemId(){
        $component	= &JComponentHelper::getComponent('com_newsletter');
        $menu		= &JSite::getMenu();
        $items		= $menu->getItems('componentid', $component->id);
        return "&Itemid=".$items[0]->id;
    }

    function isValidEmail($email){
	    return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email);
    }
}
?>
