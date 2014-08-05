jQuery(function($) {


  // If dropdown is changed

	$('#_stock_status').change(function () {

    var text = $(this).find('option:selected').attr('value');

    $('._out_of_stock_note_field').addClass('visible');

    $('.'+text+'_field').show();

    if(text == 'instock'){

      $('.outofstock_field').hide();
      $('#_out_of_stock_note').text('');

    }


  });


  // Out of Stock is selected


  if($('option[value="outofstock"]').is(":selected")){

      $('._out_of_stock_note_field, ._wc_oosm_use_global_note_field').addClass('visible');

  }


});
