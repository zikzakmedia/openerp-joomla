<?php
defined('_JEXEC') or die("Invalid access");
jimport('joomla.application.component.model');
 
class newsletterModelnewsletter extends JModel
{
    /*
    return itemid
    return array
    */
    function getItemid(){
        $component	= &JComponentHelper::getComponent('com_newsletter');
        $menu		= &JSite::getMenu();
        $items		= $menu->getItems('componentid', $component->id);

        return "&Itemid=".$items[0]->id;
    }

    /*
    list all newsletter active = 1
    return array xmlrpc
    */
	function getConnect(){
        global $mainframe;

        $openerp = new JopenobjectHelper;

        $userId = $openerp->connect();

        if(!$userId):
            $itemid = $this->getItemid();
            $mainframe->redirect( JRoute::_('index.php?option=com_newsletter&view=error'.$itemid) );
        endif;

        return array($openerp, $userId);
    }

    /*
    list all newsletter active = 1
    return array xmlrpc
    */
	function getNewsletter(){

        list($openerp, $userId) = $this->getConnect();

        # search
        $ids = $openerp->search($userId, "newsletter.newsletter", "active", "=", 1);

        # read
        $fields = array("id" => "int",
                        "complete_name" => "string",
                );
        $objs = $openerp->read($userId, "newsletter.newsletter", $ids, $fields);
        sort($objs);

		return $objs;
	}

    /*
    list all country
    return array xmlrpc
    */
	function getCountry(){

        list($openerp, $userId) = $this->getConnect();

        # search
        $ids = $openerp->search($userId, "res.country", array(), array(), array());

        # read
        $fields = array("name" => "string");

        $objs = $openerp->read($userId, "res.country", $ids, $fields);
        sort($objs);

		return $objs;
	}

    /*
    list all unsubscription reason
    return array xmlrpc
    */
	function getUnsubscriptionReason(){

        list($openerp, $userId) = newsletterModelnewsletter::getConnect();

        # search
        $ids = $openerp->search($userId, "newsletter.unsubscribe.reason", array(), array(), array());

        # read
        $fields = array(
            "id" => "int",
            "name" => "string"
        );

        $objs = $openerp->read($userId, "newsletter.unsubscribe.reason", $ids, $fields);
        sort($objs);

		return $objs;
	}

    /*
    get Partner Address ID
    return array xmlrpc
    */
	function getPartnerAddress($data){

        list($openerp, $userId) = newsletterModelnewsletter::getConnect();

        # search
        $ids = $openerp->search($userId, "res.partner.address", array("email","name"),  array("=","="), array($data['email'],$data['name']) );

        # read
        $fields = array(
            "id" => "int",
            "name" => "string"
        );

        $objs = $openerp->read($userId, "res.partner.address", $ids, $fields);

		return $objs;
	}

    /*
    create partner & partner_adreess
    return id address
    */
	function createPartner($data){

        list($openerp, $userId) = $this->getConnect();

        # search
        $ids = $openerp->search($userId, "res.partner.address", "email", "=", $data['email']);

        if(count($ids)>0):
		    $id_address = $ids[0]->scalarval();
        else:
            #create partner
            $values = array(
                    'name'  => array($data['name'], "string")
            );

            $id_partner = $openerp->create($userId, "res.partner", $values);

            #create partner address
            $values = array(
                    'name'  => array($data['name'], "string"),
                    'email'  => array($data['email'], "string"),
		            'partner_id' => array($id_partner, "int"),
		            'street' => array($data['street'], "string"),
		            'zip' => array($data['zip'], "string"),
		            'phone' => array($data['phone'], "string"),
		            'city' => array($data['city'], "string"),
                    'country_id' => array($data['country'], "int"),
            );

            $id_address = $openerp->create($userId, "res.partner.address", $values);
        endif;

        return $id_address;
	}

    /*
    create newsletter or unsubscription if not select
    $id_address = int
    $newsletters = array
    return True
    */
    function createSubscription($id_address, $newsletters){
        global $mainframe;

        $pparams = &$mainframe->getParams('com_newsletter');
        $newsletter_unsubscribe_reason_id = $pparams->get('newsletter_unsubscribe_reason');

        list($openerp, $userId) = $this->getConnect();

        # check if unsubscribe reason exist
        if($newsletter_unsubscribe_reason_id):
            $unsubscribe_reason_id = $openerp->search($userId, "newsletter.unsubscribe.reason", "id", "=", $newsletter_unsubscribe_reason_id);
        endif;
        if(count($unsubscribe_reason_id)>0):
            $unsubscribe_reason_id = True;
        else:
            $unsubscribe_reason_id = False;
        endif;

        # ============================================================================================
        # if not select newsletter but are subscription old => unsubscription for this partner.address
        # ============================================================================================
        $ids = $openerp->search($userId, "newsletter.subscription", "partner_address_id", "=", $id_address);

        # read actual newsletter subscription
        $fields = array(
            "newsletter_id" => "string",
        );
        $objs = $openerp->read($userId, "newsletter.subscription", $ids, $fields);

        $newsletter_subscription_ids = array();
        foreach($objs as $obj):
            $obj = $obj->scalarval();
            $newsletter_subscription_id = $obj["newsletter_id"]->scalarval();
            $newsletter_subscription_ids[] = $newsletter_subscription_id[0]->scalarval();
        endforeach;

        sort($newsletter_subscription_ids);
        sort($newsletters);

        $i=0; $j=0;
        while( ($i<count($newsletter_subscription_ids)) && ($j<count($newsletters)) ):
            #nothing. Are subscription and select
            if($newsletter_subscription_ids[$i] == $newsletters[$j]):
                $i++; $j++;
            endif;

            # unsubscription
            if($newsletter_subscription_ids[$i] < $newsletters[$j]):
                $subscription_id = $openerp->search($userId, "newsletter.subscription", array("partner_address_id","newsletter_id"), array("=","="), array($id_address,$newsletter_subscription_ids[$i]));
                $subscription_id = $subscription_id[0]->scalarval();

                if($unsubscribe_reason_id):
                    $values = array(
                            'newsletter_unsubscribe' => array("1", "int"),
                            'newsletter_unsubscribe_reason_id' => array( $newsletter_unsubscribe_reason_id, "int"),
                    );
                else:
                    $values = array(
                            'newsletter_unsubscribe' => array("1", "int"),
                    );
                endif;
                $openerp->write($userId, "newsletter.subscription", array(new xmlrpcval($subscription_id, "int")), $values);

                $i++;
            endif;        

            # subscription
            if($newsletter_subscription_ids[$i] > $newsletters[$j]):
                $values = array(
                        'newsletter_id' => array($newsletters[$j], "int") ,
                        'partner_address_id' => array($id_address, "int")
                );
                $id_partner = $openerp->create($userId, "newsletter.subscription", $values);

                $j++;
            endif;    
        endwhile;

        # End old subscription check => unsubscription
        while($i < count($newsletter_subscription_ids)):
                $subscription_id = $openerp->search($userId, "newsletter.subscription", array("partner_address_id","newsletter_id"), array("=","="), array($id_address,$newsletter_subscription_ids[$i]));
                $subscription_id = $subscription_id[0]->scalarval();

                if($unsubscribe_reason_id):
                    $values = array(
                            'newsletter_unsubscribe' => array("1", "int"),
                            'newsletter_unsubscribe_reason_id' => array( $newsletter_unsubscribe_reason_id, "int"),
                    );
                else:
                    $values = array(
                            'newsletter_unsubscribe' => array("1", "int"),
                    );
                endif;
                $openerp->write($userId, "newsletter.subscription", array(new xmlrpcval($subscription_id, "int")), $values);

                $i++;
        endwhile;

        # End new subscription check => subscription
        while($j<count($newsletters)):
                $values = array(
                        'newsletter_id' => array($newsletters[$j], "int") ,
                        'partner_address_id' => array($id_address, "int")
                );
                $id_partner = $openerp->create($userId, "newsletter.subscription", $values);
                $j++;
        endwhile;

        return True;
    }

    /*
    unsubscription all newsletter
    $id_address = int
    return True
    */
    function createUnsubscription($id_address){
        global $mainframe;

        $pparams = &$mainframe->getParams('com_newsletter');
        $newsletter_unsubscribe_reason_id = $pparams->get('newsletter_unsubscribe_reason');

        list($openerp, $userId) = $this->getConnect();

        # check if unsubscribe reason exist
        if($newsletter_unsubscribe_reason_id):
            $unsubscribe_reason_id = $openerp->search($userId, "newsletter.unsubscribe.reason", "id", "=", $newsletter_unsubscribe_reason_id);
        endif;
        if(count($unsubscribe_reason_id)>0):
            $unsubscribe_reason_id = True;
        else:
            $unsubscribe_reason_id = False;
        endif;

        $ids = $openerp->search($userId, "newsletter.subscription", "partner_address_id", "=", $id_address);

        if(count($ids>0)):
            foreach($ids as $id):
                $subscription_id = $id->scalarval();

                if($unsubscribe_reason_id):
                    $values = array(
                            'newsletter_unsubscribe' => array("1", "int"),
                            'newsletter_unsubscribe_reason_id' => array( $newsletter_unsubscribe_reason_id, "int"),
                    );
                else:
                    $values = array(
                            'newsletter_unsubscribe' => array("1", "int"),
                    );
                endif;

                $openerp->write($userId, "newsletter.subscription", array(new xmlrpcval($subscription_id, "int")), $values);
            endforeach;
        endif;  

        return True;
    }

}
?>
