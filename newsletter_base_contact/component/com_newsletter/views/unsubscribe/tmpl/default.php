<h2 class="componentheading"><?php echo JText::_( 'Unsubscription' ) ?></h2>

<form class="cmxform" id="newsletterForm" method="post" action="">
	<fieldset>
		<legend><?=JText::_( 'Your details' );?></legend>
		<p>
            <label for="name" class="partner"><?=JText::_( 'First Name' );?><span class="required">*</span></label>
            <input type="text" name="first_name" id="first_name" value="" class="input-text required" minlength="2" />
        </p>
		<p>
            <label for="name" class="partner"><?=JText::_( 'Name' );?><span class="required">*</span></label>
            <input type="text" name="name" id="name" value="" class="input-text required" minlength="2" />
        </p>
		<p>
            <label for="email" class="partner"><?=JText::_( 'Email' );?><span class="required">*</span></label>
            <input type="text" name="email" id="email" value="" class="input-text required email" />
		</p>
	</fieldset>

	<input type="hidden" name="option" value="com_newsletter" />
	<input type="hidden" name="task" value="unsubscribe" />
	<input type="hidden" name="view" value="unsubscribeconfirm" />
	<?php echo JHTML::_( 'form.token' ); ?>

<div id="newsleeter_send">
    <button class="button validate" type="submit"><?php echo JText::_('Send Unsubscription') ?></button>
</div>

</form>
