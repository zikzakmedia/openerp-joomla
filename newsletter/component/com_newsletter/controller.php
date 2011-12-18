<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
 
class NewsletterController extends JController
{
	function __construct()
	{
		parent::__construct();
	}

    function display($cachable=false)
    {
        $cachable = True;
        $document =& JFactory::getDocument();
 
        $viewType       = $document->getType();
        $viewName       = JRequest::getCmd( 'view', $this->getName() );
        $viewLayout     = JRequest::getCmd( 'layout', 'default' );
 
        $view = & $this->getView( $viewName, $viewType, '', array( 'base_path'=>$this->_basePath));
 
        // Get/Create the model
        if ($model = & $this->getModel($viewName)) {
            // Push the model into the view (as default)
            $view->setModel($model, true);
        }
 
        // Set the layout
        $view->setLayout($viewLayout);
 
        // Display the view
        if ($cachable && $viewType != 'feed') {
            global $option;
            $cache =& JFactory::getCache($option, 'view');
            $cache->get($view, 'display');
        } else {
            $view->display();
        }
    }

    function subscribe(){
        global $mainframe;

        JRequest::checkToken() or die( 'Invalid Token' );

        $db =& JFactory::getDBO();

        $data = array();
        if(JRequest::getVar( 'name','','post')) $data['name'] = JRequest::getVar( 'name','','post');
        if(JRequest::getVar( 'street','','post')) $data['street'] = JRequest::getVar( 'street','','post');
        if(JRequest::getVar( 'city','','post')) $data['city'] = JRequest::getVar( 'city','','post');
        if(JRequest::getVar( 'zip','','post')) $data['zip'] = JRequest::getVar( 'zip','','post');
        if(JRequest::getVar( 'country','','post')) $data['country'] = JRequest::getVar( 'country','','post');
        if(JRequest::getVar( 'phone','','post')) $data['phone'] = JRequest::getVar( 'phone','','post');
        if(JRequest::getVar( 'email','','post')) $data['email'] = JRequest::getVar( 'email','','post');

        if(JRequest::getVar( 'cid', array(), 'post', 'array')):
            $newsletters = JRequest::getVar( 'cid', array(0), 'post', 'array');
            for($i=0;$i<count($newsletters);$i++):
                $data['newsletter'.$i] = "ID: ".$newsletters[$i];
            endfor;
        endif;

        # save session
        $_SESSION['data'] = $data;

        # check email
echo $_SESSION['data']['email'];
        if(!NewsletterHelperSubscribe::isValidEmail($_SESSION['data']['email'])):
            $itemid = NewsletterHelperSubscribe::_itemId();
            $msg = JText::_( 'Email is not valid. Repeat again' );
            $mainframe->redirect( JRoute::_('index.php?option=com_newsletter'.$itemid), $msg );
        endif;

        # check subscription select
        if(!count(JRequest::getVar( 'cid', array(), 'post', 'array'))>0):
            $itemid = NewsletterHelperSubscribe::_itemId();
            $msg = JText::_( 'Select one item for your subscription. Repeat again' );
            $mainframe->redirect( JRoute::_('index.php?option=com_newsletter'.$itemid), $msg );
        endif;

		$model =& $this->getModel();
	    jimport('joomla.utilities.date');

	    $datenow = new JDate();
	    $datenow = $datenow->toFormat("%Y-%m-%d %H:%M:%S");

        # jopenobject. create partner
        $id_address = $model->createPartner($data);
        $data['partner_address_id'] = $id_address;

        if($id_address){
            # jopenobject. create subscription
            $subscription_ids = $model->createSubscription($id_address, $newsletters);

		    # Update newsletter_log
		    $row = new newsletter_log( $db );
            $row->name = $data['name'];
            $row->email = $data['email'];
            $row->created = $datenow;
            $row->data = serialize($data);
            $row->subscribe = 1;

		    if (!$row->store()) {
                $itemid = NewsletterHelperSubscribe::_itemId();
                $msg = sprintf ( JText::_( 'Error: %s. Repeat again or contact us' ), $row->getError());
                $mainframe->redirect( JRoute::_('index.php?option=com_newsletter'.$itemid), $msg );
		    }
            # delete session
            $data = array();
            $_SESSION['data'] = $data;
        } else {
            $itemid = NewsletterHelperSubscribe::_itemId();
            $msg = JText::_( 'Error create your contact address. Repeat again or contact us' );
            $mainframe->redirect( JRoute::_('index.php?option=com_newsletter'.$itemid), $msg );
        }

		parent::display();
    }

    function unsubscribe(){
        global $mainframe;

        JRequest::checkToken() or die( 'Invalid Token' );

        $db =& JFactory::getDBO();

        $data = array();
        if(JRequest::getVar( 'name','','post')) $data['name'] = JRequest::getVar( 'name','','post');
        if(JRequest::getVar( 'email','','post')) $data['email'] = JRequest::getVar( 'email','','post');

        # save session
        $_SESSION['data'] = $data;

        # check email
        if(!NewsletterHelperSubscribe::isValidEmail($data['email'])):
            $itemid = NewsletterHelperSubscribe::_itemId();
            $msg = JText::_( 'Email is not valid. Repeat again' );
            $mainframe->redirect( JRoute::_('index.php?option=com_newsletter&view=unsubscribe'.$itemid), $msg );
        endif;

		$model =& $this->getModel();
	    jimport('joomla.utilities.date');

	    $datenow = new JDate();
	    $datenow = $datenow->toFormat("%Y-%m-%d %H:%M:%S");

        # jopenobject. Unsubscribe
        $id_address = $model->getPartnerAddress($data);

        if(count($id_address)>0){
            $id_address = $id_address[0]->scalarval();
            $id_address = $id_address['id']->scalarval();

            # jopenobject. create unsubscription
            $subscription_ids = $model->createUnsubscription($id_address);

		    # Update newsletter_log
		    $row = new newsletter_log( $db );
            $row->name = $data['name'];
            $row->email = $data['email'];
            $row->created = $datenow;
            $row->data = serialize($data);
            $row->subscribe = 2;

		    if (!$row->store()) {
                $itemid = NewsletterHelperSubscribe::_itemId();
                $msg = sprintf ( JText::_( 'Error: %s. Repeat again or contact us' ), $row->getError());
                $mainframe->redirect( JRoute::_('index.php?option=com_newsletter&view=unsubscribe'.$itemid), $msg );
		    }
            # delete session
            $data = array();
            $_SESSION['data'] = $data;
        } else {
            $itemid = NewsletterHelperSubscribe::_itemId();
            $msg = JText::_( 'Error. This name and email not exist. Repeat again or contact us' );
            $mainframe->redirect( JRoute::_('index.php?option=com_newsletter&view=unsubscribe'.$itemid), $msg );
        }

		parent::display();
    }

}

?>
