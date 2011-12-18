<?php
defined( '_JEXEC') or die( 'Restricted access');
jimport( 'joomla.application.component.view');

class NewsletterViewSubscribe extends JView
{
	function display($tpl = null)
	{
        $document = & JFactory::getDocument();
        $document->setTitle(JText::_( 'Newsletter' ).' - '.JText::_( 'Subscription successfully' ));

		$this->assignRef('name', JRequest::getVar( 'name','','post'));
		$this->assignRef('email', JRequest::getVar( 'email','','post'));

        $_SESSION['data'] = '';
		parent::display($tpl);
	}

}
	
?>
