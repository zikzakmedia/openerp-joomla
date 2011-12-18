<?php
defined( '_JEXEC') or die( 'Restricted access');
jimport( 'joomla.application.component.view');

class NewsletterViewUnsubscribe extends JView
{
	function display($tpl = null)
	{
        $document = & JFactory::getDocument();
        $document->setTitle(JText::_( 'Newsletter' ).' - '.JText::_( 'Unsubscription' ));

		parent::display($tpl);
	}

}
	
?>
