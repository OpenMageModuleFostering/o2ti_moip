fechar = function(){
	jQuery('.transparente-payment-method-content').slideUp();
}
function setCcType(ccType)
{
	if(ccType != 'HI'){
		jQuery('#transparente_cc_type').val(ccType);
		jQuery('#credito_numero').addClass('validate-cc-number');
		jQuery('#credito_numero').addClass('validate-cc-type');
	} else {
		jQuery('#credito_numero').removeClass('validate-cc-number');
		jQuery('#credito_numero').removeClass('validate-cc-type');
		
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
jQuery(document).ready(function() {
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
});