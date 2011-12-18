<?php
defined( '_JEXEC') or die( 'Restricted access');

jimport( 'joomla.application.component.view');

class NewsletterViewNewsletter extends JView
{
	function display($tpl = null)
	{
        global $mainframe;

		$document 	= & JFactory::getDocument();
        $pparams = &$mainframe->getParams('com_newsletter');
        
        $pagetitle = $pparams->get('newsletter_title') ? $pparams->get('newsletter_title') : JText::_( 'Newsletter' );
		$mainframe->setPageTitle( $pagetitle );
   		$mainframe->addMetaTag( 'title' , $pagetitle );
        if($pparams->get('metakey')) $document->setMetadata( 'keywords' , $pparams->get('metakey') );
        if($pparams->get('metadesc')) $document->setMetadata( 'description' , $pparams->get('metadesc')  );

        JHTML::_('behavior.mootools');
        $document->addScript(JURI::base().'components/com_newsletter/assets/js/jquery.js');
        $document->addScript(JURI::base().'components/com_newsletter/assets/js/jquery.validate.js');
        $document->addCustomTag('<script type="text/javascript">$(document).ready(function() { $("#newsletterForm").validate();});</script>');

		$model =& $this->getModel();
		$newsletters = $model->getNewsletter();
        $countrys = $model->getCountry();

		$this->assignRef('newstitle' , $pparams->get('newsletter_title') );
		$this->assignRef('newstop' , $pparams->get('newsletter_top') );
		$this->assignRef('newsletters' , $newsletters );
		$this->assignRef('countrys' , $countrys );
		$this->assignRef('newsbottom' , $pparams->get('newsletter_bottom') );

		parent::display($tpl);
	}

}
	
?>
