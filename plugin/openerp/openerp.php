<?php
/**
 * @version		$Id
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin' );
jimport('joomla.application.component.helper');
jimport('joomla.user.helper');

class plgXMLRPCOpenerp extends JPlugin
{

	function plgXMLRPCOpenerp(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}

	/**
	* @return array An array of associative arrays defining the available methods
	*/
	function onGetWebServices()
	{
		global $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString, $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct, $xmlrpcValue;

		return array
		(
				'openerp.test' => array(
				'function' => 'plgXMLRPCOpenerpServices::test',
				'docstring' => JText::_('Test web services'),
				'signature' => array(array ($xmlrpcString))
			),
			    'openerp.getUserInfo' => array(
				'function' => 'plgXMLRPCOpenerpServices::getUserInfo',
				'docstring' => JText::_('Returns information about an author in the system.'),
				'signature' => array(array ($xmlrpcBoolean,$xmlrpcString,$xmlrpcString))
			),
		        'openerp.getTable' => array(
			    'function' => 'plgXMLRPCOpenerpServices::getTable',
				'docstring' => JText::_('Select table.'),
			    'signature' => array(array($xmlrpcBoolean,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcValue))
			),
		        'openerp.setTable' => array(
			    'function' => 'plgXMLRPCOpenerpServices::setTable',
				'docstring' => JText::_('Insert fields table.'),
			    'signature' => array(array($xmlrpcBoolean,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcValue))
			),
		        'openerp.resetTable' => array(
			    'function' => 'plgXMLRPCOpenerpServices::resetTable',
				'docstring' => JText::_('Change value = 0.'),
			    'signature' => array(array($xmlrpcBoolean,$xmlrpcString,$xmlrpcString,$xmlrpcString))
			),
		        'openerp.deleteTable' => array(
			    'function' => 'plgXMLRPCOpenerpServices::deleteTable',
				'docstring' => JText::_('Delete items where changed=0 and condition.'),
			    'signature' => array(array($xmlrpcBoolean,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString))
			),
		        'openerp.deleteItems' => array(
			    'function' => 'plgXMLRPCOpenerpServices::deleteItems',
				'docstring' => JText::_('Delete items only by condition.'),
			    'signature' => array(array($xmlrpcBoolean,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcArray,$xmlrpcString))
			),
		);
	}
}

class plgXMLRPCOpenerpServices
{
	/* Testing function */
	function test ()
	{
		return "It works";
	}

	/* Debug function */
	function debug($s) {
		// load plugin params info
	 	$plugin =& JPluginHelper::getPlugin('xmlrpc','openerp');
	 	$params = new JParameter( $plugin->params );
		$debug = $params->get('debug');

        if($debug);
            $config = &JFactory::getConfig();
            $log_path = $config->getValue('log_path');
            $log_file = $log_path.DS."openerp";

	        jimport('joomla.utilities.date');

	        $datenow = new JDate();
	        $datenow = $datenow->toFormat("%Y-%m-%d %H:%M:%S");

		    $fp = fopen($log_file,"a+");
		    fwrite($fp, "[".$datenow."] ".$s."\n");
		    fclose($fp);
        enfid;
	}

	function authenticateUser($username, $password)
	{
		$db =& JFactory::getDBO();

        $query = 'SELECT id FROM #__users WHERE username = "'.$username.'" AND gid = 25 AND block = 0';
        $db->setQuery( $query );
        $result = $db->loadResult();

        if($result){
		    jimport( 'joomla.user.authentication');
		    $auth = & JAuthentication::getInstance();
		    $credentials = array( 'username' => $username, 'password' => $password );
		    $options = array();
		    $response = $auth->authenticate($credentials, $options);
		    return $response->status === JAUTHENTICATE_STATUS_SUCCESS;
        } else {
            return $response->status === JAUTHENTICATE_STATUS_FAILURE;
        }
	}

	/* ================== */
	/* Services functions */
	/* ================== */
	function getUserInfo($username, $password)
	{
		global $xmlrpcerruser, $xmlrpcStruct;

        plgXMLRPCOpenerpServices::debug("getUserInfo");

		if(!plgXMLRPCOpenerpServices::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$user =& JUser::getInstance($username);

		$struct = new xmlrpcval(
		array(
			'nickname'	=> new xmlrpcval($user->get('username')),
			'userid'	=> new xmlrpcval($user->get('id')),
			'email'		=> new xmlrpcval($user->get('email')),
			'name'	    => new xmlrpcval($user->get('name')),
		), $xmlrpcStruct);

		return new xmlrpcresp($struct);
	}

	function getTable($username, $password, $table, $oerp_data) {
		global $xmlrpcerruser, $xmlrpcStruct;

        plgXMLRPCOpenerpServices::debug("getTable");

		if(!plgXMLRPCOpenerpServices::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$db =& JFactory::getDBO();

		$datas=array();

		$qry = "";
		foreach ($oerp_data as $field=>$type):
			$qry .= $field.", ";
        endforeach;

		$qry = substr($qry, 0, strlen($qry)-2);

		$query = "SELECT ".$qry." FROM #__".$table;
        $db->setQuery( $query );
        $rows = $db->loadObjectList();

        plgXMLRPCOpenerpServices::debug("getTable: ".$query);

        if(count($rows) > 0):
            $data=array();
            foreach($rows as $row):
                foreach($oerp_data as $field=>$type):
                    $data[] = new xmlrpcval($row->$field, $type);
                    plgXMLRPCOpenerpServices::debug("setTable: ".$row->$field." ".$type.": ");
                endforeach;
				$datas[]=new xmlrpcval($data, "array");
            endforeach;
        endif;

		return new xmlrpcresp(new xmlrpcval($datas, "array"));
	}

	function setTable($username, $password, $table, $oerp_data) {
		global $xmlrpcerruser, $xmlrpcStruct;

        plgXMLRPCOpenerpServices::debug("setTable");

		if(!plgXMLRPCOpenerpServices::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$db =& JFactory::getDBO();

		$new = 0;
		$query = "SELECT count(*) FROM #__".$table." WHERE id=". $oerp_data['id'];
        plgXMLRPCOpenerpServices::debug("setTable: ". $query);
        $db->setQuery( $query );
        $count = $db->loadResult();
		if (! $count ) {
			$new = 1;
			$query = "INSERT INTO #__".$table." (id) VALUES (". $oerp_data['id'] .")";
            plgXMLRPCOpenerpServices::debug("setTable: ". $query);
            $db->setQuery( $query );
            if (!$db->query()) {
                    plgXMLRPCOpenerpServices::debug("setTable - ".$db->getErrorMsg());
            }
		}

		// Delete attached files
		$path = "media/".strtr($table,"_","/")."/".$oerp_data['id']; // Directory to store the attached files
		foreach (glob($path."/*") as $file) {
			if (is_file($file)) {
				unlink($file);
			}
		}

		$qry = "";
		foreach ($oerp_data as $field=>$value)
			if (substr($field,0,5) == "fname") { // is an image attached file (fname => full name, picture => image file)
				$filename = $value;
				$extension = strrchr($value,'.');
				$field_picture = "picture".substr($field,5);
				@mkdir($path, 0700, true);
				// Guardem la imatge
				//file_put_contents($filename, base64_decode("media/radiotv/".$table."/".$filename));
				if (!$hd=fopen($path."/".$filename, "wb")) continue;
				fwrite($hd, base64_decode($oerp_data[$field_picture]));
				fclose($hd);
				// Construeix i guarda miniatura de la imatge
				$newxsize=115;
				$newysize=1000;
				$load='imagecreatefrom'.substr($extension,1,strlen($extension)-1);
				$save='image'.substr($extension,1,strlen($extension)-1);
				$tmp_img=$load($path."/".$filename);
				$imgsize = getimagesize($path."/".$filename);
				if ($imgsize[0] > $newxsize || $imgsize[1] > $newysize) {
					if ($imgsize[0]*$newysize > $imgsize[1]*$newxsize) {
						$ratio=$imgsize[0]/$newxsize;
					} else {
						$ratio=$imgsize[1]/$newysize;
					}
				} else {
					$ratio=1;
				}
				//debug($imgsize[0]." ".$imgsize[1]." ".$ratio);
				$tn=imagecreatetruecolor (floor($imgsize[0]/$ratio),floor($imgsize[1]/$ratio));
				imagecopyresized($tn,$tmp_img,0,0,0,0,floor($imgsize[0]/$ratio),floor($imgsize[1]/$ratio),$imgsize[0],$imgsize[1]);
				//$save($tn, $path."/thumb_".$filename);
				if( !is_dir( $path."/thumbs" ) )
					mkdir( $path."/thumbs",  0777);
				$save($tn, $path."/thumbs/".$filename);

			} elseif (substr($field,0,7) == "picture") { // file of the image attached file. It has been processed in the last if()

			} elseif (substr($field,-4) == "_ids") {
			   	$t = explode("_", $table);
				$table1 = $t[1];
				$table2 = substr($field, 0, strlen($field)-4);

			  	// If many2many relation: insert in auxiliar table
				// Example of the auxiliar table name: radiotv_channel_program_rel (channel < program) 
				if (strcasecmp($table1, $table2) < 0)
				   $tablerel = $t[0]."_".$table1."_".$table2."_rel";
				else {
				   $tablerel = $t[0]."_".$table2."_".$table1."_rel";
				}
			    $query = "DELETE FROM #__".$tablerel." WHERE ".$table1."_id = ".$oerp_data['id'];
                plgXMLRPCOpenerpServices::debug("setTable: ". $query);
                $db->setQuery( $query );
                if (!$db->query()) {
                        plgXMLRPCOpenerpServices::debug("setTable - ".$db->getErrorMsg());
                }

				foreach ($value as $v) {
			        $query = "INSERT INTO #__".$tablerel." (".$table1."_id, ".$table2."_id) VALUES (".$oerp_data['id'].",".$v.")";
                    plgXMLRPCOpenerpServices::debug("setTable: ". $query);
                    $db->setQuery( $query );
                    if (!$db->query()) {
                            plgXMLRPCOpenerpServices::debug("setTable - ".$db->getErrorMsg());
                    }
				}

			  	// If one2many relation: insert in related table
			    $query = "UPDATE #__".$t[0]."_".$table2." SET ".$table1."_id=0 WHERE ".$table1."_id=".$oerp_data['id'];
                plgXMLRPCOpenerpServices::debug("setTable: ". $query);
                $db->setQuery( $query );
                if (!$db->query()) {
                        plgXMLRPCOpenerpServices::debug("setTable - ".$db->getErrorMsg());
                }

				foreach ($value as $v) {
			        $query = "UPDATE #__".$t[0]."_".$table2." SET ".$table1."_id=".$oerp_data['id']." WHERE id=".$v;
                    plgXMLRPCOpenerpServices::debug("setTable: ". $query);
                    $db->setQuery( $query );
                    if (!$db->query()) {
                            plgXMLRPCOpenerpServices::debug("setTable - ".$db->getErrorMsg());
                    }
				}

			} else { // normal field
				$qry .= $field. "='" .addcslashes($value,"'"). "',";
			}

        $query = "UPDATE #__".$table." SET ".$qry." changed=1 WHERE id=".$oerp_data['id'];
        plgXMLRPCOpenerpServices::debug("setTable: ". $query);
        $db->setQuery( $query );
        if (!$db->query()) {
                plgXMLRPCOpenerpServices::debug("setTable - ".$db->getErrorMsg());
        }
		return new xmlrpcresp(new xmlrpcval($new, "int"));
	}

	function resetTable($username, $password, $table) {
		global $xmlrpcerruser, $xmlrpcStruct;

        plgXMLRPCOpenerpServices::debug("resetTable");

		if(!plgXMLRPCOpenerpServices::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$db =& JFactory::getDBO();

        $query = "UPDATE #__".$table." SET changed=0";
        $db->setQuery( $query );
        if (!$db->query()) {
                plgXMLRPCOpenerpServices::debug("setTable - ".$db->getErrorMsg());
        }

		return new xmlrpcresp(new xmlrpcval(1, "int"));
	}

	function deleteTable($username, $password, $table, $phpfilter) {
		global $xmlrpcerruser, $xmlrpcStruct;

        plgXMLRPCOpenerpServices::debug("deleteTable");

		if(!plgXMLRPCOpenerpServices::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}

		$db =& JFactory::getDBO();
        $delete = 0;

        if(count($phpfilter) > 0):
            if ($phpfilter != "") $where = " AND ".$phpfilter;

		    $query = "SELECT count(*) FROM #__".$table." WHERE changed=0" .$where;
            $db->setQuery( $query );
            $count = $db->loadResult();
		    if($count) {
			    // Delete attached files
		        $query = "SELECT id FROM #__".$table." WHERE changed=0" .$where;
                $db->setQuery( $query );
                $count = $db->loadObjectList();

                foreach($rows as $row):
				    $path = "media/".strtr($table,"_","/")."/".$row[0]; // Directory to store the attached files
				    foreach (glob($path."/*") as $file) {
					    if (is_file($file)) {
						    unlink($file);
					    }
				    }
                endforeach;
			    // Delete records
                $query = "DELETE FROM #__".$table." WHERE changed=0" .$where;
                $db->setQuery( $query );
                if (!$db->query()) {
                        plgXMLRPCOpenerpServices::debug("setTable - ".$db->getErrorMsg());
                }
                $delete++;
		    }
        endif;
		return new xmlrpcresp(new xmlrpcval($delete, "int"));
	}

	function deleteItems($username, $password, $table, $items, $field) {
		// By default $field="id"
		global $xmlrpcerruser, $xmlrpcStruct;

        plgXMLRPCOpenerpServices::debug("deleteItems");

		if(!plgXMLRPCOpenerpServices::authenticateUser($username, $password)) {
			return new xmlrpcresp(0, $xmlrpcerruser+1, JText::_("Login Failed"));
		}
plgXMLRPCOpenerpServices::debug("deleteItems: DINS");
		$db =& JFactory::getDBO();

		$delete = 0;
		foreach ($items as $id) {
			// Delete attached files
			$path = "media/".strtr($table,"_","/")."/".$id; // Directory to store the attached files
			foreach (glob($path."/*") as $file) {
				if (is_file($file)) {
					unlink($file);
				}
			}
			// Delete record
            $query = "DELETE FROM #__".$table." WHERE ".$field."=" .$id;
            $db->setQuery( $query );
            if (!$db->query()) {
                    plgXMLRPCOpenerpServices::debug("setTable - ".$db->getErrorMsg());
            }
			$delete++;
plgXMLRPCOpenerpServices::debug("deleteItems: ". $query);
		}
		return new xmlrpcresp(new xmlrpcval($delete, "int"));
	}

}
