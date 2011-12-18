<?php
#    Copyright (c) 2010 Zikzakmedia S.L. (http://zikzakmedia.com) 
#       All Rights Reserved, Raimon Esteve <resteve@zikzakmedia.com>
#
#    ableton is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    ableton is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the Ableton file screen
 *
 * @package Joomla
 * @subpackage Ableton
 * @since 1.5
 */
class NewsletterViewLog extends JView {

	function display($tpl = null)
	{
		global $mainframe;

		//Load behavior
		jimport('joomla.html.pane');
		JHTML::_('behavior.tooltip');

        $db		=& JFactory::getDBO();

#		//initialise variables
		$editor 	= & JFactory::getEditor();
		$document	= & JFactory::getDocument();
		$pane 		= & JPane::getInstance('sliders');
		$user 		= & JFactory::getUser();

#		//get vars
		$cid		= JRequest::getVar( 'cid' );
		$task		= JRequest::getVar('task');
		$url 		= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		//build toolbar
		if ( $cid ) {
			JToolBarHelper::title( JText::_( 'View Log' ), 'config.png' );
		} else {
			JToolBarHelper::title( JText::_( 'Log' ), 'config.png' );
		}
		JToolBarHelper::cancel();

		//get data from model
		$model		= & $this->getModel();
		$row     	= & $this->get( 'Data');

		//assign vars to the template
		$this->assignRef('Lists'    , $Lists);
		$this->assignRef('row'      , $row);
		$this->assignRef('conf'     , $conf);
		$this->assignRef('editor'	, $editor);
		$this->assignRef('pane'		, $pane);
		$this->assignRef('task'		, $task);

		parent::display($tpl);
	}
}
?>
