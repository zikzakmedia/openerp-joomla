<?php defined( '_JEXEC') or die( 'Restricted access'); ?>

<h2 class="componentheading"><?php  echo $this->newstitle ?></h2>

<div id="newsletter_top">
    <?php  echo $this->newstop ?>
</div>

<form class="cmxform" id="newsletterForm" method="post" action="">
	<fieldset>
		<legend><?=JText::_( 'Your details' );?></legend>
		<p>
            <label for="name" class="partner"><?=JText::_( 'Name' );?><span class="required">*</span></label>
            <input type="text" name="name" id="name" value="<?=$_SESSION['data']['name']; ?>" class="input-text required" minlength="2" />
        </p>
		<p>
            <label for="street" class="partner"><?=JText::_( 'Address' );?></label>
            <input type="text" name="street" id="street" value="<?=$_SESSION['data']['street']; ?>" class="input-text" />
		</p>
		<p>
            <label for="city" class="partner"><?=JText::_( 'City' );?></label>
            <input type="text" name="city" id="city" value="<?=$_SESSION['data']['city']; ?>" class="input-text" />
		</p>
		<p>
            <label for="zip" class="partner"><?=JText::_( 'Zip' );?></label>
            <input type="text" name="zip" id="zip" value="<?=$_SESSION['data']['zip']; ?>" class="input-text" />
		</p>
		<p>
            <label for="country" class="partner"><?=JText::_( 'Country' );?></label>
            <select name="country" id="country">
            <?
            echo '<option value="">'.JText::_( 'Select country' ).'</option>';
            foreach ($this->countrys as $country):
                $country = $country->scalarval();
                if($country["id"]->scalarval() == $_SESSION['data']['country']):
                    $selected = 'selected="selected"';
                else:
                    $selected = '';
                endif;
                echo '<option value="'.$country["id"]->scalarval().'" '.$selected.'>'.utf8_encode($country["name"]->scalarval()).'</option>';
            endforeach;
            ?>
            </select>
		</p>
		<p>
            <label for="phone" class="partner"><?=JText::_( 'Phone' );?></label>
            <input type="text" name="phone" id="phone" value="<?=$_SESSION['data']['phone']; ?>" class="input-text" />
		</p>
		<p>
            <label for="email" class="partner"><?=JText::_( 'Email' );?><span class="required">*</span></label>
            <input type="text" name="email" id="email" value="<?=$_SESSION['data']['email']; ?>" class="input-text required email" />
		</p>
	</fieldset>

<script type="text/javascript">
    $(document).ready( function() {
 
        // Select all
        $("A[href='#select_all']").click( function() {
            $("#" + $(this).attr('rel') + " INPUT[type='checkbox']").attr('checked', true);
            return false;
        });
 
        // Select none
        $("A[href='#select_none']").click( function() {
            $("#" + $(this).attr('rel') + " INPUT[type='checkbox']").attr('checked', false);
            return false;
        });
 
        // Invert selection
        $("A[href='#invert_selection']").click( function() {
            $("#" + $(this).attr('rel') + " INPUT[type='checkbox']").each( function() {
                $(this).attr('checked', !$(this).attr('checked'));
            });
            return false;
        });
 
    });
</script>

	<fieldset id="newsletter">
		<legend><?=JText::_( 'Your Subscription' );?></legend>
        <div class="newsletter_select"><a rel="newsletter" href="#select_all"><?=JText::_( 'Select All' );?></a> | <a rel="newsletter" href="#select_none"><?=JText::_( 'Select None' );?></a> | <a rel="newsletter" href="#invert_selection"><?=JText::_( 'Invert Selection' );?></a></div>
<?
if(count($this->newsletters) > 0):
    $i = 0;
    foreach ($this->newsletters as $newsletter):
        $newsletter = $newsletter->scalarval();
        $childs = explode("/",$newsletter["complete_name"]->scalarval());
        echo '<p class="parent_id'.count($childs).'">';
        ?>
            <input type="checkbox" id="newsletter<?=$i;?>" name="cid[]" value="<?=$newsletter["id"]->scalarval();?>" />
            <label for="newsletter<?=$i;?>"><?=utf8_encode($newsletter["complete_name"]->scalarval());?></label>
		</p>
        <?
        $i++;
    endforeach;
endif;
?>
	</fieldset>

	<input type="hidden" name="option" value="com_newsletter" />
	<input type="hidden" name="task" value="subscribe" />
	<input type="hidden" name="view" value="subscribe" />
	<?php echo JHTML::_( 'form.token' ); ?>

<div id="newsleeter_send">
    <button class="button validate" type="submit"><?php echo JText::_('Send Subscription') ?></button>
</div>

</form>

<div id="newsletter_bootom">
    <?php  echo $this->newsbottom ?>
</div>
