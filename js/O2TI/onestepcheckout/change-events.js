jQuery(function() {
	var flag = 1;
	var i = 1;
	var shipaddselect = jQuery("#shipping-address-select");
	var change = 0;
	var change_select = 0;
	var timer;
	var islogin = logined();
	var hasadd = hasaddress();
	jQuery("#billing-address-select").bind({
		change: function() {
			if (flag == 1) {
				change_select = 0;
				if (jQuery(this).val().length > 0) {
					countryid = "BR";
					updateBillingForm(jQuery(this).val(), flag);
				} else {
					updateBillingForm(jQuery(this).val(), flag);
				}
			} else {
				updateBillingForm(jQuery(this).val(), flag);
				change_select = 1;
			}
		}
	});
	jQuery("#shipping-address-select").bind({
		change: function() {
			if (flag == 0) {
				change_select = 1;
				if (jQuery(this).val().length > 0) {
					countryid = "BR";
					if (countryid) {
						updateShippingForm(jQuery(this).val());
					}
				} else {
					if (jQuery("#shipping-address-select").val()) {
						updateShippingForm(jQuery(this).val());
					}
				}
			}
		}
	});
	jQuery('[id="shipping:same_as_billing"]').click(function() {
				jQuery('#shipping_show').hide();
				jQuery("#o2ti-osc-p2").removeClass('onestepcheckout-numbers onestepcheckout-numbers-3').addClass('onestepcheckout-numbers onestepcheckout-numbers-2');
				jQuery("#o2ti-osc-p3").removeClass('onestepcheckout-numbers onestepcheckout-numbers-4').addClass('onestepcheckout-numbers onestepcheckout-numbers-3');
				jQuery("#o2ti-osc-p4").removeClass('onestepcheckout-numbers onestepcheckout-numbers-5').addClass('onestepcheckout-numbers onestepcheckout-numbers-4');
				flag = 1;
				shipping.setSameAsBilling(true);
				$('shipping:same_as_billing').checked = false;
				jQuery('#shipping_show').hide();
				jQuery("#ship_to_same_address").val(1);
	});
	jQuery("#ship_to_same_address").bind({
		click: function() {
			val_address = jQuery("#ship_to_same_address").val();
			shipaddselect = jQuery("#shipping-address-select");
			billaddselect = jQuery("#billing-address-select");
			if (val_address == 1) {
				jQuery("#o2ti-osc-p2").removeClass('onestepcheckout-numbers onestepcheckout-numbers-2').addClass('onestepcheckout-numbers onestepcheckout-numbers-3');
				jQuery("#o2ti-osc-p3").removeClass('onestepcheckout-numbers onestepcheckout-numbers-3').addClass('onestepcheckout-numbers onestepcheckout-numbers-4');
				jQuery("#o2ti-osc-p4").removeClass('onestepcheckout-numbers onestepcheckout-numbers-4').addClass('onestepcheckout-numbers onestepcheckout-numbers-5');
				flag = 0;
				jQuery("#shipping_show").show();
				jQuery("#ship_to_same_address").val(0);
			} else {
				jQuery('#shipping_show').hide();
				jQuery("#o2ti-osc-p2").removeClass('onestepcheckout-numbers onestepcheckout-numbers-3').addClass('onestepcheckout-numbers onestepcheckout-numbers-2');
				jQuery("#o2ti-osc-p3").removeClass('onestepcheckout-numbers onestepcheckout-numbers-4').addClass('onestepcheckout-numbers onestepcheckout-numbers-3');
				jQuery("#o2ti-osc-p4").removeClass('onestepcheckout-numbers onestepcheckout-numbers-5').addClass('onestepcheckout-numbers onestepcheckout-numbers-4');
				flag = 1;
				shipping.setSameAsBilling(true);
				$('shipping:same_as_billing').checked = false;
				jQuery('#shipping_show').hide();
				jQuery("#ship_to_same_address").val(1);
				
			}
		}
	});
	jQuery('#register_new_account').val(1);
	jQuery('#subscribe_newsletter').bind({
		click: function() {
			var val = 0;
			if (jQuery(this).is(':checked')) {
				val = 1;
			}
			jQuery(this).val(val);
		}
	});
	jQuery.fn.clearForm = function() {
		jQuery(this).find(':input').each(function() {
			switch (this.type) {
			case 'password':
			case 'text':
			case 'textarea':
				jQuery(this).val('');
				break;
			case 'checkbox':
			case 'radio':
				
			}
		});
	};
	jQuery('#allow_gift_messages').bind({
		click: function() {
			if (jQuery(this).is(':checked')) {
				jQuery('#allow-gift-message-container').show();
				} else {
					jQuery('#allow-gift-message-container').hide();
				}
		}
	});
	if (country_load()) {
		jQuery('[id="billing:country_id"]').bind({
			change: function() {
				if (flag == 1) {
					updateShippingType();
					change = 0;
				} else {
					change = 1;
				}
			}
		});
		jQuery('[id="shipping:country_id"]').bind({
			change: function() {
				if (flag == 0) {
					change = 1;
					updateShippingType();
				}
			}
		});
	}
	if (region_load()) {
		jQuery('[id="billing:region_id"]').bind({
			change: function() {
				if (flag == 1) {
					updateShippingType();
					change = 0;
				} else {
					change = 1;
				}
			}
		});
		jQuery('[id="shipping:region_id"]').bind({
			change: function() {
				if (flag == 0) {
					change = 1;
					updateShippingType();
				}
			}
		});
	}
});