<?php
/**
 * @version 1.0 $Id: default.php 958 2009-02-02 17:23:05Z julienv $
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2009 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<table cellspacing="0" cellpadding="0" border="0" width="100%" class="adminform">
	<tr>
		<td valign="top">
			<table class="adminform">
				<tr>
					<td>
						<label for="title">
							<?php echo JText::_( 'Name' ).':'; ?>
						</label>
					</td>
					<td>
						<strong><?php echo $this->row->name; ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<label for="email">
							<?php echo JText::_( 'Email' ).':'; ?>
						</label>
					</td>
					<td>
						<?php echo $this->row->email; ?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<label for="data">
							<?php echo JText::_( 'Data' ).':'; ?>
						</label>
					</td>
					<td>
					<?php
                        $data = unserialize($this->row->data);
                        foreach($data as $code => $value):
                            echo $code.": ".$value."<br/>";
                        endforeach;
                    ?>
					</td>
				</tr>
			</table>

		</td>
		<td valign="top" width="320px" style="padding: 7px 0 0 5px">

			<table width="100%">
				<tr>
					<td><strong><?php echo JText::_( 'Log ID' ).':'; ?></strong></td>
					<td><?php echo $this->row->id; ?></td>
				</tr>
				<tr>
					<td><strong><?php echo JText::_( 'Created' ).':'; ?></strong></td>
					<td><?php echo JHTML::_('date',  $this->row->created, JText::_('DATE_FORMAT_LC2') ); ?></td>
				</tr>
				<tr>
					<td><strong><?php echo JText::_( 'Subscribe' ).':'; ?></strong></td>
					<td>
                    <?php
                    if($row->subscribe == 2) echo JText::_( 'UnSubscribe' );
                    else echo JText::_( 'Subscribe' );
					?>
                    </td>
				</tr>
			</table>

		</td>
	</tr>
</table>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_newsletter" />
<input type="hidden" name="controller" value="newsletter" />
<input type="hidden" name="view" value="newsletter" />
<input type="hidden" name="task" value="" />
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>
