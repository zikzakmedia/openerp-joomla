<?php
#    Copyright (c) 2010 Zikzakmedia S.L. (http://zikzakmedia.com) 
#       All Rights Reserved, Raimon Esteve <resteve@zikzakmedia.com>
#
#    Newsletter is free software: you can redistribute it and/or modify
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

defined('_JEXEC') or die('Restricted access');

/**
 * Newsletter Model class
 *
 * @package Joomla
 * @subpackage Newsletter
 * @since 1.5
 */
class newsletter_log extends JTable
{
	/**
	 * Primary Key
	 * @var int
	 */
	var $id 		= null;
    var $name       = null;
    var $email      = null;
	var $created 	= null;
	var $data 		= null;
	var $subscribe  = null;

	function newsletter_log(& $db) {
		parent::__construct('#__newsletter_log', 'id', $db);
	}

	// overloaded check function
	function check($elsettings)
	{
		return true;
	}
}
?>
