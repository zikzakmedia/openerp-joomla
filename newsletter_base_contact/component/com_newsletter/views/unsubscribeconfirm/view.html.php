<?php
defined( '_JEXEC') or die( 'Restricted access');
jimport( 'joomla.application.component.view');

class NewsletterViewUnsubscribeconfirm extends JView
{
	function display($tpl = null)
	{
        $document = & JFactory::getDocument();
        $document->setTitle(JText::_( 'Newsletter' ).' - '.JText::_( 'Unsubscription successfully' ));

		$this->assignRef('first_name', JRequest::getVar( 'first_name','','post'));
		$this->assignRef('name', JRequest::getVar( 'name','','post'));
		$this->assignRef('email', JRequest::getVar( 'email','','post'));

        $_SESSION['data'] = '';
		parent::display($tpl);
	}

}
	
?>
