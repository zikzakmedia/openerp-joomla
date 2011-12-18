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
    get Contact ID
    return array xmlrpc
    */
	function getContact($data){

        list($openerp, $userId) = newsletterModelnewsletter::getConnect();

        # search
        $ids = $openerp->search($userId, "res.partner.contact", array("email","name","first_name"),  array("=","=","="), array($data['email'],$data['name'],$data['first_name']) );

        # read
        $fields = array(
            "id" => "int",
            "name" => "string"
        );

        $objs = $openerp->read($userId, "res.partner.contact", $ids, $fields);

		return $objs;
	}

    /*
    create contact
    return id contact
    */
	function createContact($data){

        list($openerp, $userId) = $this->getConnect();

        # search
        $ids = $openerp->search($userId, "res.partner.contact", "email", "=", $data['email']);

        if(count($ids)>0):
		    $id_contact = $ids[0]->scalarval();
        else:
            #create contact
            $values = array(
                    'name'  => array($data['name'], "string"),
                    'first_name'  => array($data['first_name'], "string"),
                    'email'  => array($data['email'], "string"),
                    'mobile' => array($data['phone'], "string"),
            );
            $id_contact = $openerp->create($userId, "res.partner.contact", $values);

            #create res.partner.address and res.partner.job
            if( $data['street'] && $data['zip'] && $id_contact):
                # search if contact are partner.address:
                # if not exist, create
                # if exist, not create or update

                $ids = $openerp->search($userId, "res.partner.job", "contact_id", "=", $id_contact);

                if(!count($ids)>0):

                    $values = array(
        		            'street' => array($data['street'], "string"),
        		            'zip' => array($data['zip'], "string"),
        		            'city' => array($data['city'], "string"),
                            'country_id' => array($data['country'], "int"),
                            'email'  => array($data['email'], "string"),
        		            'phone' => array($data['phone'], "string"),
                    );

                    $id_address = $openerp->create($userId, "res.partner.address", $values);

                    if($id_address && $id_contact):
                        $values = array(
                                'address_id' => array($id_address, "int"),
                                'contact_id' => array($id_contact, "int"),
                                'email'  => array($data['email'], "string"),
            		            'phone' => array($data['phone'], "string"),
                        );

                        $id_partner_job = $openerp->create($userId, "res.partner.job", $values);
                    endif;

                endif;
 
            endif;
        endif;

        return $id_contact;
	}

    /*
    create newsletter or unsubscription if not select
    $id_contact = int
    $newsletters = array
    return True
    */
    function createSubscription($id_contact, $newsletters){
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
        # if not select newsletter but are subscription old => unsubscription for this partner.contact
        # ============================================================================================
        $ids = $openerp->search($userId, "newsletter.subscription", "partner_contact_id", "=", $id_contact);

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
                $subscription_id = $openerp->search($userId, "newsletter.subscription", array("partner_contact_id","newsletter_id"), array("=","="), array($id_contact,$newsletter_subscription_ids[$i]));
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
                # search if subscription exist
                $ids = $openerp->search($userId, "newsletter.subscription", array("partner_contact_id","newsletter_id"), array("=","="), array($id_contact,$newsletters[$j]));

                if(count($ids)>0):
		            $id_newsletter_subscription = $ids[0]->scalarval();
                    $values = array(
                            'newsletter_unsubscribe' => array(0, "int"),
                            'newsletter_unsubscribe_reason_id' => array('', "int"),
                    );
                    $openerp->write($userId, "newsletter.subscription", array(new xmlrpcval($id_newsletter_subscription, "int")), $values);
                else:
                    $values = array(
                            'newsletter_id' => array($newsletters[$j], "int"),
                            'partner_contact_id' => array($id_contact, "int"),
                    );
                    $openerp->create($userId, "newsletter.subscription", $values);
                endif;

                $j++;
            endif;    
        endwhile;

        # End old subscription check => unsubscription
        while($i < count($newsletter_subscription_ids)):
                $subscription_id = $openerp->search($userId, "newsletter.subscription", array("partner_contact_id","newsletter_id"), array("=","="), array($id_contact,$newsletter_subscription_ids[$i]));
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
                # search if subscription exist
                $ids = $openerp->search($userId, "newsletter.subscription", array("partner_contact_id","newsletter_id"), array("=","="), array($id_contact,$newsletters[$j]));

                if(count($ids)>0):
		            $id_newsletter_subscription = $ids[0]->scalarval();
                    $values = array(
                            'newsletter_unsubscribe' => array(0, "int"),
                            'newsletter_unsubscribe_reason_id' => array('', "int"),
                    );
                    $openerp->write($userId, "newsletter.subscription", array(new xmlrpcval($id_newsletter_subscription, "int")), $values);
                else:
                    $values = array(
                            'newsletter_id' => array($newsletters[$j], "int"),
                            'partner_contact_id' => array($id_contact, "int"),
                    );
                    $openerp->create($userId, "newsletter.subscription", $values);
                endif;

                $j++;
        endwhile;

        return True;
    }

    /*
    unsubscription all newsletter
    $id_contact = int
    return True
    */
    function createUnsubscription($id_contact){
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

        $ids = $openerp->search($userId, "newsletter.subscription", "partner_contact_id", "=", $id_contact);

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
