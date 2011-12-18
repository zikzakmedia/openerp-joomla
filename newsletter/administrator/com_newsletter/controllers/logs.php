<?php
#    Copyright (c) 2010 Zikzakmedia S.L. (http://zikzakmedia.com) 
#       All Rights Reserved, Raimon Esteve <resteve@zikzakmedia.com>
#
#    newsletter is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    newsletter is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Newsletter Component Events Controller
 *
 * @package Joomla
 * @subpackage Newsletter
 * @since 0.9
 */
class NewsletterControllerLogs extends newsletterController
{
	function __construct()
	{
		parent::__construct();
	}

	function edit( )
	{
		JRequest::setVar( 'view', 'log' );
		JRequest::setVar( 'hidemainmenu', 1 );

		$model 	= $this->getModel('log');

		parent::display();
	}

	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$this->setRedirect( 'index.php?option=com_newsletter&view=newsletter' );
	}
}
?>
