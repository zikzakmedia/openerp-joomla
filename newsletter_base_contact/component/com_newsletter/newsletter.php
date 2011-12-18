<?php
defined( '_JEXEC' ) or die( 'Restricted access' ); 

// Require the controller
require_once(JPATH_COMPONENT.DS.'controller.php');
require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_newsletter'.DS.'tables'.DS.'newsletter_log.php');

// helpers
require_once(JPATH_ROOT.DS.'components'.DS.'com_newsletter'.DS.'helpers'.DS.'subscribe.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_jopenobject'.DS.'helpers'.DS.'helper.php');

// Create the controller
$classname  = 'NewsletterController';
$controller = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getVar('task', null, 'default', 'cmd') );

// Redirect if set by the controller
$controller->redirect();
?>
