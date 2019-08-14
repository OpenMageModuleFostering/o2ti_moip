jQuery('#credito_portador_telefone').mask("(00)0000-0000Z", {placeholder: "(__)____-_____",translation: {'Z': {pattern: /[0-9]/, optional: true}}});
jQuery('#credito_portador_cpf').mask("000.000.000-00", {placeholder: "___.___.___-__"});
jQuery('#credito_portador_nascimento').mask("99/99/9999", {placeholder: "__/__/____"}); 
jQuery('.input-brand-bandeira > li').bind({
  click: function() {
    jQuery(this).parent().find('input[type="radio"]').prop('checked',false);
    jQuery(this).parent().find('img').hide();
    jQuery(this).find('img').show();
    jQuery(this).fadeTo(0, 1);
    jQuery(this).find('input[type="radio"]').prop('checked',true);
  }
});
	
jQuery('.input-switcher > li').not('.transparente-payment-method-content').bind({
	click: function() {
      jQuery(this).parent().find('input[type="radio"]').prop('checked',false);
      jQuery('#checkout-payment-bandeira').hide();
      jQuery('#checkout-payment-banco').hide();
      jQuery(this).find('input[type="radio"]').prop('checked',true);
    }
});

jQuery('.transparente-payment-methods > li').not('.transparente-payment-method-content').bind({
  click: function() {
    jQuery('.transparente-payment-method-content').not('#' + jQuery(this).attr('data-target')).slideUp();
    jQuery('#' + jQuery(this).attr('data-target')).slideDown();
  }
});


jQuery("input.radio.payment_method_handle:checked").on({
	click: function() {
	  if (jQuery(this).val() == 'moip_transparente_standard') {
	    showBlocks();
	  } else {
	    hideBlocks();
	  }
	}
});
jQuery("#credito_expiracao_ano").change(function() {
 var d = new Date();
    var m = d.getMonth()+1;
    var str= d.getFullYear()+'';
	y_atual= str.match(/\d{2}$/);
	var select_y = jQuery(this).val();
	var select_m = jQuery("#credito_expiracao_mes").val();
 
	if(select_y == y_atual){
		if(select_m < m ){
			jQuery(".alerta_data").show();
			jQuery(".alerta_data").html('<ul class="messages"><li class="error-msg"><ul><li><span>Seu cartão está expirado ou com data incorreta. Sua transação não será aceita, por favor corriga o dado.</span></li></ul></li></ul>');
			jQuery("#checkout-onepage-buttom").attr("disabled","disabled");
		}
		else{
			jQuery(".alerta_data").hide();
			jQuery("#checkout-onepage-buttom").attr("disabled");
		}
	}
	else{
			jQuery(".alerta_data").hide();
			jQuery("#checkout-onepage-buttom").removeAttr("disabled");
		}
});
jQuery("#credito_expiracao_mes").change(function() {
 	var d = new Date();
    var m = d.getMonth()+1;
    var str= d.getFullYear()+'';
	y_atual= str.match(/\d{2}$/);
	var select_m = jQuery(this).val();
	var select_y = jQuery("#credito_expiracao_ano").val();
	if(select_y == y_atual && select_y != ""){
		if(select_m < m ){
			jQuery(".alerta_data").show();
			jQuery(".alerta_data").html('<ul class="messages"><li class="error-msg"><ul><li><span>Seu cartão está expirado ou com data incorreta. Sua transação não será aceita, por favor corriga o dado.</span></li></ul></li></ul>');
		}
		else{
			jQuery(".alerta_data").hide();
		}
	}
	else{
			jQuery(".alerta_data").hide();
		}
});
var creditcards = { 
    list:[
        {
            brand:          'American Express',
            value_brand:    'Amex-moip',
            verification:   '^3[47][0-9]',
            separation:     '^([0-9]{4})([0-9]{6})?(?:([0-9]{6})([0-9]{5}))?$',
            hidden:         '**** ****** *[0-9][0-9][0-9][0-9]',
            accepted:       true,
            length:         15
        },
        {
            brand:          'MasterCard',
            value_brand:    'Mastercard-moip',
            verification:   '^5[1-5][0-9]',
            separation:     '^([0-9]{4})([0-9]{4})?([0-9]{4})?([0-9]{4})?$',
            hidden:         '**** **** **** [0-9][0-9][0-9][0-9]',
            accepted:       true,
            length:         16
        },
        {
            brand:          'Visa',
            value_brand:    'Visa-moip',
            verification:   '^4[0-9]',
            separation:     '^([0-9]{4})([0-9]{4})?([0-9]{4})?([0-9]{4})?$',
            hidden:         '**** **** **** [0-9][0-9][0-9][0-9]',
            accepted:       true,
            length:         16
        },
        {
            brand:          'Hipercard',
            value_brand:    'Hipercard-moip',
            verification:   '^606282|3841(?:0[0-9])[0-9]',
            separation:     '^([0-9]{19})?$',
            hidden:         '*****************',
            accepted:       true,
            length:         19
        },
        {
            brand:          'Diners Club',
            value_brand:    'Dinners-moip',
            verification:   '^3(?:0[0-5]|[68][0-9])[0-9]',
            separation:     '^([0-9]{4})([0-9]{4})?([0-9]{4})?(?:([0-9]{4})([0-9]{4})([0-9]{2}))?$',
            hidden:         '**** **** **[0-9][0-9] [0-9][0-9]',
            accepted:       true,
            length:         14
        }
        
    ], 
    active:null 
};
jQuery('#credito_numero').keydown(function(e){
	var card = jQuery(this).val().replace(/[^0-9]/g,''),
	    trim = jQuery.trim( jQuery(this).val().slice(0,-1) );
	for( var i=0; i<creditcards.list.length; i++ ){
	  if(card.match( new RegExp(creditcards.list[i].verification) )){
	    creditcards.active = i;
	    if( jQuery(this).next('img').length == 0 ){
	      jQuery(this).next('small').remove();
	      jQuery("."+creditcards.list[i].value_brand).trigger('click');
	    }
	    if( !creditcards.list[i].accepted && jQuery(this).nextAll('small').length == 0 ){
	     
	    }
	    break;
	  }
	}
	if( creditcards.active == null && card.length > 4 && jQuery(this).nextAll('small').length == 0 ){
	  jQuery(this).after('<small style="margin-left:5px; color:#F00;">'+'Cartão Inválido'+'</small>');
	  jQuery('.input-brand-bandeira > li').find('img').show();
	}
	key = creditcards.active !== null? creditcards.active : 1 ;
	if( e.keyCode == 8 && trim != jQuery(this).val().slice(0,-1) ){
	  jQuery(this).val( trim );
	  e.preventDefault();
	  return;
	}
	if( card.length >= creditcards.list[ key ].length && jQuery.inArray(e.keyCode, [37, 38, 39, 40, 46, 8, 9, 27, 13, 110, 190]) === -1 && !e.metaKey && !e.ctrlKey ){
	  e.preventDefault();
	  return;
	}
	if( new RegExp(creditcards.list[ key ].separation).exec( card ) && e.keyCode >= 48 && e.keyCode <= 57 ){
	  jQuery(this).val( jQuery(this).val() + ' ' );
	}
	return;
});
jQuery('#credito_numero').keyup(function(e){
	var card = jQuery(this).val().replace(/[^0-9]/,'');
	if( creditcards.active !== null && !card.match( new RegExp(creditcards.list[ creditcards.active ].verification) ) ){
	    jQuery(this).nextAll('small').remove();
	    jQuery(this).next('img').remove();
	    creditcards.active = null;
	}else
	if( card.length < 4 ){
	  jQuery(this).next('small').remove();
	}
});
jQuery('#credito_numero').on('paste',function(e){
	var el    = this;
    setTimeout(function(){
      var card = jQuery(el).val().replace(/[^0-9]/g,'');
      jQuery(el).val( card );
      var e = jQuery.Event('keydown',{
        which:    37,
        keyCode:  37
      });
      jQuery(el).trigger(e).promise().done(function(e){
        key = creditcards.active !== null? creditcards.active : 1 ;
        card.substr( 0 , creditcards.list[ key ].length );
        var separation  = new RegExp(creditcards.list[ key ].separation).exec( card ),
            storage     = '';
        while( !separation && card.length > 1 ){
          storage     = card.charAt( card.length - 1 );
          card        = card.slice(0,-1);
          separation  = new RegExp(creditcards.list[ key ].separation).exec( card );
        }
        if( separation ){
          var separated = [];
          for( var i=0; i<separation.length; i++){
            if( typeof separation[i] != 'undefined' ) separated.push( separation[i] );
          }
          var string = separated.slice(1).join(' ') + (storage!=''? ' '+storage : '' )
          jQuery(el).val( string )
        }        
      });
    },0);
});

jQuery('#onestep_form :input').blur(function() {
	if(jQuery(this).attr('id') != "billing:day" && jQuery(this).attr('id') != "billing:month"){
		Validation.validate(jQuery(this).attr('id'));
	}
});
jQuery('.tooltip-transparente-handler').hover(function() {
	var hover_class = jQuery(this).parent().find('.tooltip-transparente').attr('class').split(' ').pop();
	jQuery('.'+hover_class).addClass('tooltip-transparente-visible');
	setTimeout(function(){jQuery('.'+hover_class).removeClass('tooltip-transparente-visible');}, 5000);
},function() {
	jQuery('.tooltip-transparente').removeClass('tooltip-transparente-visible');
});
function setCcType(ccType)
{
  if(ccType != 'HI'){
    jQuery('#transparente_cc_type').val(ccType);
    jQuery('#credito_numero').addClass('validate-cc-number');
    jQuery('#credito_numero').addClass('validate-cc-type');
    jQuery('#credito_codigo_seguranca').mask("ZZZ", {placeholder: "999",translation: {'Z': {pattern: /[0-9]/, optional: true}}}); 
  } else {
    jQuery('#credito_numero').removeClass('validate-cc-number');
    jQuery('#credito_numero').removeClass('validate-cc-type');
    jQuery('#credito_codigo_seguranca').mask("999Z", {placeholder: "9999",translation: {'Z': {pattern: /[0-9]/, optional: true}}}); 
  }

}
if(Validation) {
  if($H != 'HI'){
    Validation.creditCartTypes = $H({
        'VI': [new RegExp('^4[0-9]{12}([0-9]{3})?$'), new RegExp('^[0-9]{3}$'), true],
        'MC': [new RegExp('^5[1-5][0-9]{14}$'), new RegExp('^[0-9]{3}$'), true],
        'AE': [new RegExp('^3[47][0-9]{13}$'), new RegExp('^[0-9]{4}$'), true],
        'HI': [new RegExp('/^(606282\d{10}(\d{3})?)|(3841\d{15})$/'), new  RegExp('^[0-9]{3}$'), true],
        'DI': [false, new RegExp('^[0-9]+'), true]
    });
  }
}
function countChar(val) {
  var cvv = val.value.length;
  if (cvv > 2) {
    jQuery(".dados-titular").slideDown("slow");
    jQuery("#formcli").slideDown("slow");
    
    jQuery('.dados-titular').css({
      display: "block"
    });
    jQuery("#formcli").css({
      display: "block"
    });
    document.getElementById('credito_portador_nome').value = document.getElementById('billing:firstname').value + ' ' + document.getElementById('billing:lastname').value;
    document.getElementById('credito_portador_telefone').value = document.getElementById('billing:telephone').value;
    document.getElementById('credito_portador_cpf').value = document.getElementById('billing:taxvat').value;
    if (document.getElementById('billing:year').value) {
      document.getElementById('credito_portador_nascimento').value = document.getElementById('billing:day').value + '/' + document.getElementById('billing:month').value + '/' + document.getElementById('billing:year').value
    }
  }
};

function hideBlocks(){
  jQuery('.transparente-payment-method-content').css({'display':'none'});
  jQuery('ul.transparente-payment-methods').css({'display':'none'});
  jQuery('#transparente-cartao, #transparente-boleto, #transparente-transferencia').children().hide();
}
function Cofre(cofre_brand, numero_cofre){
    jQuery("#brand_cofre").val(cofre_brand);
    jQuery("#cofre_numero").val(numero_cofre);
    
}
function Novo(){
    if(jQuery("#new_card").is(":checked")){
      jQuery(".form-list-cofre").hide();
      jQuery(".cartao").show();
      jQuery(".formcli").show();
      jQuery("#use_cofre").val('0');
    }
}
function showBlocks(cofre){
  jQuery('ul.transparente-payment-methods li').first().click();
  jQuery('ul.transparente-payment-methods').css({'display':'block'});
  if(cofre =="false"){
    jQuery('#transparente-cartao, #transparente-boleto, #transparente-transferencia').children().show();
  } else {
    jQuery('#transparente-boleto, #transparente-transferencia').children().show();
    jQuery('#transparente-cartao, .form-list-cofre').show();
  }
}
function forcecofre(){
    jQuery(".cartao").hide();
    jQuery(".formcli").hide();
}
function forceselectMoip(){
  if(jQuery("input.radio.payment_method_handle:checked").val() == "moip_transparente_standard"){
    showBlocks();
  } else {
    hideBlocks();
  }
  if(jQuery("input[name=payment\\[method\\]]:checked").val() == "moip_transparente_standard" && jQuery("input[name=payment\\[forma_pagamento\\]]:checked").val() != ""){
      jQuery("#cartao_radio").trigger("click");
      jQuery("#transparente-cartao").css({'display':'block'})
  } else {
      jQuery("input[name=payment\\[forma_pagamento\\]]:checked").val();
      jQuery("#transparente-cartao").css({'display':'none'});
  }
}