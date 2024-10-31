<?php
/*
Author: JB Braendel
Plugin Name: Tax Calculator
Plugin URI: http://www.polarisplace.com
Description: A Tax Calculator widget, to quickly and easily calculate your property tax, and be able to see where your tax is going to.
Version: 1.0
Author URI: http://www.polarisplace.com/
*/

class Tax_Calculator extends WP_Widget {
	
	/*
	
	NOTES:
		* jQuery is a much better / more efficient solution (obviously), however Wordpress has some mysterious settings w/ jQuery, turning it off and on. Until this is controllable, we will use pure javascript ... The functionality for jQuery is simply commented out
		* Calculator is Javascript based -- both user and admin MUST have javascript enabled in order to use this widget
		* When you add a new widget somewhere, its not added UNTIL you click "Save" ... this means if you add the widget to a sidebar, and start clicking "Add Field", it will add fields to the INACTIVE widget since thats still where it thinks the widget is at
	
	
	
	WIDGET SETTINGS:
	
		num_fields   : (int) number of fields
		
		FIELDS,
		   fieldName
			id =   widget-jb_widget-7-fieldName1
			name = widget-jb_widget[7][fieldName1]
			
		   fieldNum
			id   = widget-jb_widget-7-fieldNum1
			name = widget-jb_widget[7][fieldNum1]
	
	*/
	
	function Tax_Calculator() {
		
		$widget_options = array('classname'    =>  'Tax Calculator',
								'description'  =>  __('Descriptive property tax calculations'));
							
		$control_options = array('height'      => 300,
								 'width'       => 250);
							
		$this->WP_Widget( 'Tax_Calculator', __('Property Tax Calculator'), $widget_options, $control_options );
	}
	
	function widget($args, $instance) {
		// Generate HTML
		
		extract($args, EXTR_SKIP);
		
		// Input for user to put property value
		$numFields = $instance['numFields'];
		?>
        
        <form action="javascript:TC_calculateTax();">
        	 Property Value: <input type="text" id="<?php echo $this->get_field_id('TC_property_value'); ?>" />
             <input type="submit" value="Calculate Property Tax" />
        </form>
        
        <div id="<?php echo $this->get_field_id('TC_tax_info'); ?>"></div>
	<a onclick="TC_clear();">clear taxinfo</a>
		
		
        <script language="javascript">
			var taxInfoField = "<?php echo $this->get_field_id('TC_tax_info'); ?>";
			var numFields = <?php echo $instance['numFields']; ?>;
			var fieldNames = new Array(<?php
			// Fill the fieldName array
			for ($i = 0; $i < $numFields; $i++)
			{
				echo '"'.$instance['fieldName'.$i].'"';
				
				// Add a comma for the next fieldName
				if ($i+1 < $numFields)
					echo ",";
			}
		?>);
			var fieldNums = new Array(<?php
			// Fill the fieldName array
			$totalTax = 0;
			for ($i = 0; $i < $numFields; $i++)
			{
				$totalTax += (float)$instance['fieldNum'.$i];
				echo $instance['fieldNum'.$i];
				
				// Add a comma for the next fieldName
				if ($i+1 < $numFields)
					echo ",";
			}
		?>);
		
			var totalTax = <?php echo $totalTax; ?>;

		// Clear the DIV
		function TC_clear() {
			document.getElementById("<?php echo $this->get_field_id('TC_tax_info'); ?>").innerHTML="";
			document.getElementById("<?php echo $this->get_field_id('TC_property_value'); ?>").value="";
		}

		
		// Calculate each of the taxes, and print them out to the user
		function TC_calculateTax() {
			var divField = document.getElementById("<?php echo $this->get_field_id('TC_tax_info'); ?>");
			var value = document.getElementById("<?php echo $this->get_field_id('TC_property_value'); ?>").value;
			value = parseFloat(value);
			if (isNaN(value) || value < 0)
			{
				divField.innerHTML = "Please enter a valid number"
				return;
			}
			
			var displayedContent = "<br/><br/>	Results for property with Current Assessment Value of $" + value;
			displayedContent += "<br/><br/>";
			displayedContent += "<table><th colspan=2>Property Taxes</th>";
			displayedContent += "<tr><td style='padding-right:3px;'>Total Taxes:</td><td>Of which:</td></tr>";
			displayedContent += "<tr><td>$" + TC_taxValue(value, totalTax) + "</td><td>";
			
			for(var i = 0; i < numFields; i++)
			{
				displayedContent += "$" + TC_taxValue(value, fieldNums[i]) + " goes to <strong>" + fieldNames[i] + "</strong><br/>";
			}
			displayedContent += "</td></tr></table>";
			
			displayedContent += "<br/><br/><ul>";
			for(var i = 0; i < numFields; i++)
			{
				displayedContent += "<li>" + "Your <strong>" + fieldNames[i] + "</strong> tax rate is: " + fieldNums[i] + "%</li>";
			}
			displayedContent += "<li>Your <strong>Total</strong> tax rate is: " + totalTax + "%</li>";
			displayedContent += "</ul>";
			
			divField.innerHTML = displayedContent;
		}
		
		// Find the tax of a given value
		function TC_taxValue(value, taxable) {
			return TC_setNum(value * (taxable/100));
		}
		
		// Set the given number to 2 decimal places (scrape off the rest)
		function TC_setNum(num) {
			num *= 100;
			num = Math.round(num);
			num = parseInt(num);
			num /= 100;
			num = num.toFixed(2);
			return num;
		}
		
		function roundNumber(num, dec) {
			var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
			return result;
		}
		
		</script>
		
		<?php
	}
	
	function form($instance) {
		// User interface for configuring widget
		
        $num_fields = $instance['numFields']; // Number of fields
        if (is_nan($num_fields) or empty($num_fields) or $num_fields <= 0)
        {
			$instance['numFields'] = 1;
			$num_fields = 1;
        }
        ?>	
        
        <script language="javascript">
		
			var numFields = <?php echo $num_fields; ?>;
			
			// Set the generic field id's / name's ;  This will make it easier for adding/removing/renaming fields
			var fieldName_id   = "<?php echo $this->get_field_id('fieldName'); ?>";
			var fieldName_name = "<?php echo substr($this->get_field_name('fieldName'), 0, strlen($this->get_field_name('fieldName'))-1); ?>";
			var fieldNum_id    = "<?php echo $this->get_field_id('fieldNum'); ?>";
			var fieldNum_name  = "<?php echo substr($this->get_field_name('fieldNum'), 0, strlen($this->get_field_name('fieldNum'))-1); ?>";
			var fieldRemove_id = "<?php echo $this->get_field_id('X'); ?>";
			var fieldBr_id     = "<?php echo $this->get_field_id('BR'); ?>";
			
			// Add a field into the form
			function TC_addField() {
				// jQuery Way
				// ----------------
				//$('#<?php echo $this->get_field_id('showFields'); ?>').append('<input type="text" id="'+fieldName_id+''+numFields+'" name="'+fieldName_name+''+numFields+']" /> <input type="text" id="'+fieldNum_id+''+numFields+'" name="'+fieldNum_name+''+numFields+']" style="width:50px;" value="0" /> <a class="ntdelbutton" id="post_tag-check-num-'+numFields+'" onclick="javascript:removeField('+numFields+');" style="float:right;">X</a><br/>');
				// ----------------
				
				
				
				// Javascript Way
				// ----------------
				
				var divField = document.getElementById('<?php echo $this->get_field_id('showFields'); ?>');
				
					// Field Input name
				var newElement = document.createElement('input');
				newElement.type = 'text';
				newElement.id = fieldName_id+''+numFields;
				newElement.name = fieldName_name+''+numFields+']';
				divField.appendChild(newElement);
				
					// Field Input number
				newElement = document.createElement('input');
				newElement.type = 'text';
				newElement.id = fieldNum_id+''+numFields;
				newElement.name = fieldNum_name+''+numFields+']';
				newElement.setAttribute('style', "width:50px; margin-left:5px;");
				newElement.value = "0";
				divField.appendChild(newElement);
				
					// Field A x-out button
				newElement = document.createElement('a');
				newElement.id = fieldRemove_id+''+numFields;
				newElement.setAttribute('className', 'ntdelbutton');
				newElement.setAttribute('onclick', 'javascript:removeField('+numFields+');');
				newElement.setAttribute('style', "float:right;");
				newElement.innerHTML = 'X';
				divField.appendChild(newElement);
				
					// Field break
				newElement = document.createElement('br');
				newElement.id = fieldBr_id+''+numFields;
				divField.appendChild(newElement);
				
				// ---------------
				
				
				numFields++;
				document.getElementById('<?php echo $this->get_field_id('numFields'); ?>').value = numFields;
			}
			
			function removeField(num) {
				
				// Only 1 field -- remove the information but NOT the field itself
				if (num == 0 && numFields == 1)
				{
					document.getElementById(fieldName_id+''+num).value = '';
					document.getElementById(fieldNum_id+''+num).value = '0';
					return;	
				}
				
				var divFields = document.getElementById('<?php echo $this->get_field_id('showFields'); ?>');
				divFields.removeChild(document.getElementById(fieldName_id+''+num));
				divFields.removeChild(document.getElementById(fieldNum_id+''+num));
				divFields.removeChild(document.getElementById(fieldRemove_id+''+num));
				divFields.removeChild(document.getElementById(fieldBr_id+''+num));
				
				for (var i = num+1; i < numFields; i++) {
					
					// Bump down the fieldName fields
					document.getElementById(fieldName_id+''+i).name = fieldName_name+''+(i-1)+']';
					document.getElementById(fieldName_id+''+i).id = fieldName_id+''+(i-1);
					//$('#'+fieldName_id+''+i).attr('name', fieldName_name+''+(i-1)+']');	
					//$('#'+fieldName_id+''+i).attr('id', fieldName_id+''+(i-1));
					
					// Bump down the fieldNum fields
					document.getElementById(fieldNum_id+''+i).name = fieldNum_name+''+(i-1)+']';
					document.getElementById(fieldNum_id+''+i).id = fieldNum_id+''+(i-1);
					//$('#'+fieldNum_id+''+i).attr('name', fieldNum_name+''+(i-1)+']');	
					//$('#'+fieldNum_id+''+i).attr('id', fieldNum_id+''+(i-1));
					
					// Bump down the   X   remove button
					document.getElementById(fieldRemove_id+''+i).setAttribute('onclick', "javascript:removeField("+(i-1)+");");
					document.getElementById(fieldRemove_id+''+i).id = fieldRemove_id+''+(i-1);
					//$('#post_tag-check-num-'+i).attr('onclick', 'javascript:removeField('+(i-1)+');');
					//$('#post_tag-check-num-'+i).attr('id', 'post_tag-check-num-'+(i-1));
					
					document.getElementById(fieldBr_id+''+i).id = fieldBr_id+''+(i-1);
				}
				
				numFields--;
				document.getElementById('<?php echo $this->get_field_id('numFields'); ?>').value = numFields;
				//$('#<?php echo $this->get_field_id('numFields'); ?>').attr('value', numFields);
			}
		</script>
        
        <form action="" name="">
        	<input type="hidden" id="<?php echo $this->get_field_id('numFields'); ?>" name="<?php echo $this->get_field_name('numFields'); ?>" value="<?php echo $instance['numFields']; ?>"  />
            
            <div id="<?php echo $this->get_field_id('showFields'); ?>">
			<?php
			
			for($i = 0; $i < $num_fields; $i++)
			{
				?>
                <input type="text" id="<?php echo $this->get_field_id('fieldName'.$i); ?>" name="<?php echo $this->get_field_name('fieldName'.$i); ?>" value="<?php echo $instance['fieldName'.$i]; ?>"  />
				<input type="text" id="<?php echo $this->get_field_id('fieldNum'.$i); ?>" name="<?php echo $this->get_field_name('fieldNum'.$i); ?>" value="<?php echo $instance['fieldNum'.$i]; ?>" style="width:50px;"  />
				 <a class="ntdelbutton" id="<?php echo $this->get_field_id('X'.$i); ?>" onclick="javascript:removeField(<?php echo $i; ?>);" style="float:right;">X</a><br id="<?php echo $this->get_field_id('BR'.$i); ?>"/><?php	
			}
				
			
			
			?>
            </div>
            
            <br/>
            <input type="button" name="addfield" class="button-primary" onclick="javascript:TC_addField();" value="Add Field" />
        </form>
        
        <?php
	}
	
	function update($new_instance, $old_instance) {
		// Saves configuration data
		
		$numFields = $new_instance['numFields'];
		for ($i = 0; $i < $numFields; $i++)
		{
			$badChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+`~'\"\\/?><,;:[]{}| ";
			$badChars = str_split($badChars);
			$fieldNum = str_split($new_instance['fieldNum'.$i]);
			$new_instance['fieldNum'.$i] = "";
			$setDecimal = FALSE;
			foreach ($fieldNum as $char)
			{
				if ($char == '.')
				{
					if (!$setDecimal)
						$setDecimal=TRUE;
					else
						continue;
				}
				
				if (!in_array($char, $badChars))
					$new_instance['fieldNum'.$i] .= $char;
			}
			
			if (empty($new_instance['fieldNum'.$i]))
				$new_instance['fieldNum'.$i] = 0;
		}
		
		return $new_instance;
	}

}

function tax_calculator_widget_init() {
	register_widget('Tax_Calculator');
}

add_action('widgets_init', 'tax_calculator_widget_init');

?>