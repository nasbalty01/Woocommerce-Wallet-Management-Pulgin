jQuery(function ($) {
 

    $('#user_roles').select2({
     
        allowClear: true
        
    });

    $('#order_statuses_select').select2({
     
        allowClear: true
        
    });
    $('#selected_categories').select2({
     
        allowClear: true
        
    });

    $(document).on('change', '#cashback_type', function(){
        var cashback_type = $(this).val();
        if(cashback_type === 'fixed'){
            $('.min_max_both').hide();
        }
        else{
            $('.min_max_both').show();
        }
    });

    function cashback_type_check(){
        var cashback_type = $('#cashback_type').val();
        if(cashback_type === 'fixed'){
            $('.min_max_both').hide();
            $('#min_amount').removeAttr('required');
            $('#max_amount').removeAttr('required');
        }
        else{
            $('.min_max_both').show();
            $('#min_amount').attr('required', 'required');
            $('#max_amount').attr('required', 'required');
        }
    }
    cashback_type_check();

    $(document).on('change', '#cashback_for', function(){
         var cashback_for = $(this).val();
         if(cashback_for === 'products'){
           $('#cart_total_from_label').text('Minimum amount of purchase product');
           $('#cart_total_to_label').text('Maximum amount of purchase product');
           $('.cart_total_from_des').text('Enter the minimum amount of purchase product cashback.');
           $('.cart_total_to_des').text('Enter the maximum amount of purchase product cashback.');
           $('.product_category').show();
           $('.cart_mess').hide();
           $('.products_mess').show();
           $('.min_max').hide();
           $('#products_message').attr('required', 'required');
           $('#cart_message').removeAttr('required');
           $('#cart_total_from').removeAttr('required');
           $('#cart_total_to').removeAttr('required');
          
         }
         else if(cashback_for === 'cart'){
            $('#cart_total_from_label').text('Minimum amount of cart subtotal');
            $('#cart_total_to_label').text('Maximum amount of cart subtotal');
            $('.cart_total_from_des').text('Enter the minimum amount of cart subtotal cashback.');
            $('.cart_total_to_des').text('Enter the maximum amount of cart subtotal cashback.');
            $('.product_category').hide();
            $('.products_mess').hide();
            $('.cart_mess').show();
            $('.min_max').show();
            $('#cart_message').attr('required', 'required');
            $('#products_message').removeAttr('required');
            $('#cart_total_from').attr('required', 'required');
            $('#cart_total_to').attr('required', 'required');
         }
         else if(cashback_for === 'recharge'){
            $('#cart_total_from_label').text('Minimum amount of recharge wallet');
            $('#cart_total_to_label').text('Maximum amount of recharge wallet');
            $('.cart_total_from_des').text('Enter the minimum amount of recharge wallet cashback.');
            $('.cart_total_to_des').text('Enter the maximum amount of recharge wallet cashback.');
            $('.product_category').hide();
            $('.products_mess').hide();
            $('.cart_mess').hide();
            $('.min_max').show();
            $('#products_message').removeAttr('required');
            $('#cart_message').removeAttr('required');
            $('#cart_total_from').attr('required', 'required');
            $('#cart_total_to').attr('required', 'required');
         }
         else if(cashback_for === 'last order'){
            $('#cart_total_from_label').text('Minimum amount of last purchase subtotal');
            $('#cart_total_to_label').text('Maximum amount of last purchase subtotal');
            $('.cart_total_from_des').text('Enter the minimum amount oof last purchase subtotal cashback.');
            $('.cart_total_to_des').text('Enter the maximum amount of last purchase subtotal cashback.');
            $('.product_category').hide();
            $('.products_mess').hide();
            $('.cart_mess').hide();
            $('.min_max').show();
            $('#products_message').removeAttr('required');
            $('#cart_message').removeAttr('required');
            $('#cart_total_from').attr('required', 'required');
            $('#cart_total_to').attr('required', 'required');
         }
         else if(cashback_for === 'purchase history'){
            $('#cart_total_from_label').text('Minimum amount of purchase history');
            $('#cart_total_to_label').text('Maximum amount of purchase history');
            $('.cart_total_from_des').text('Enter the minimum amount of purchase history cashback.');
            $('.cart_total_to_des').text('Enter the maximum amount of purchase history cashback.');
            $('.product_category').hide();
            $('.products_mess').hide();
            $('.cart_mess').hide();
            $('.min_max').show();
            $('#products_message').removeAttr('required');
            $('#cart_message').removeAttr('required');
            $('#cart_total_from').attr('required', 'required');
            $('#cart_total_to').attr('required', 'required');
         }
	    });
    
       function cashback_for_check(){
            var cashback_for = $('#cashback_for').val();
            if(cashback_for === 'products'){
                $('#cart_total_from_label').text('Minimum amount of purchase product');
                $('#cart_total_to_label').text('Maximum amount of purchase product');
                $('.cart_total_from_des').text('Enter the minimum amount of purchase product cashback.');
                $('.cart_total_to_des').text('Enter the maximum amount of purchase product cashback.');
                $('.product_category').show();
                $('.cart_mess').hide();
                $('.products_mess').show();
                $('.min_max').hide();
                $('#products_message').attr('required', 'required');
                $('#cart_message').removeAttr('required');
                $('#cart_total_from').removeAttr('required');
                $('#cart_total_to').removeAttr('required');
               
              }
              else if(cashback_for === 'cart'){
                 $('#cart_total_from_label').text('Minimum amount of cart subtotal');
                 $('#cart_total_to_label').text('Maximum amount of cart subtotal');
                 $('.cart_total_from_des').text('Enter the minimum amount of cart subtotal cashback.');
                 $('.cart_total_to_des').text('Enter the maximum amount of cart subtotal cashback.');
                 $('.product_category').hide();
                 $('.products_mess').hide();
                 $('.cart_mess').show();
                 $('.min_max').show();
                 $('#cart_message').attr('required', 'required');
                 $('#products_message').removeAttr('required');
                 $('#cart_total_from').attr('required', 'required');
                 $('#cart_total_to').attr('required', 'required');
              }
              else if(cashback_for === 'recharge'){
                 $('#cart_total_from_label').text('Minimum amount of recharge wallet');
                 $('#cart_total_to_label').text('Maximum amount of recharge wallet');
                 $('.cart_total_from_des').text('Enter the minimum amount of recharge wallet cashback.');
                 $('.cart_total_to_des').text('Enter the maximum amount of recharge wallet cashback.');
                 $('.product_category').hide();
                 $('.products_mess').hide();
                 $('.cart_mess').hide();
                 $('.min_max').show();
                 $('#products_message').removeAttr('required');
                 $('#cart_message').removeAttr('required');
                 $('#cart_total_from').attr('required', 'required');
                 $('#cart_total_to').attr('required', 'required');
              }
              else if(cashback_for === 'last order'){
                 $('#cart_total_from_label').text('Minimum amount of last purchase subtotal');
                 $('#cart_total_to_label').text('Maximum amount of last purchase subtotal');
                 $('.cart_total_from_des').text('Enter the minimum amount of last purchase subtotal cashback.');
                 $('.cart_total_to_des').text('Enter the maximum amount of last purchase subtotal cashback.');
                 $('.product_category').hide();
                 $('.products_mess').hide();
                 $('.cart_mess').hide();
                 $('.min_max').show();
                 $('#products_message').removeAttr('required');
                 $('#cart_message').removeAttr('required');
                 $('#cart_total_from').attr('required', 'required');
                 $('#cart_total_to').attr('required', 'required');
              }
              else if(cashback_for === 'purchase history'){
                 $('#cart_total_from_label').text('Minimum amount of purchase history');
                 $('#cart_total_to_label').text('Maximum amount of purchase history');
                 $('.cart_total_from_des').text('Enter the minimum amount of purchase history cashback.');
                 $('.cart_total_to_des').text('Enter the maximum amount of purchase history cashback.');
                 $('.product_category').hide();
                 $('.products_mess').hide();
                 $('.cart_mess').hide();
                 $('.min_max').show();
                 $('#products_message').removeAttr('required');
                 $('#cart_message').removeAttr('required');
                 $('#cart_total_from').attr('required', 'required');
                 $('#cart_total_to').attr('required', 'required');
              }
           }

      cashback_for_check();

    $('.js_multipage_select_product').select2({
        ajax: {
            dataType: "json",
            url: ajaxurl,
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    action: 'post_purchase_offer_getproductsearch' 
                };
            },
            processResults: function( data ) {
                var options = [];
                if ( data ) {
                    $.each( data, function( index, text ) { 
                        options.push( { id: text[0], text: text[1]  } );

                    });
                                // console.log(options);
                }
                return {
                    results: options
                };
                            // console.log(options);
            },
            cache: true
        },
        minimumInputLength: 3
    });
    
    // $('.origin').each(function() {
    //     if ($(this).text().trim() === 'Unknown') {
    //         $(this).closest('tr').hide();
    //     }
    // });


    function wallet_transfer(receiver_email, sender_email, pay_amount, transaction_type, transaction_action, transaction_reference, pay_note, end_date, statueses) {

        $.ajax({
            url:ajaxurl,
            type: 'POST',
            data: {
                action: 'create_order_admin',
                nonce: php_var.nonce,
                receiver_email: receiver_email,
                sender_email: sender_email,
                transaction_type: transaction_type,
                transaction_action: transaction_action,
                transaction_reference: transaction_reference,
                pay_amount: pay_amount,
                pay_note: pay_note,
                end_date: end_date,
                status: statueses

            },
            success: function(response) {
                if (response.success) {
                    $('#wm_manual_add_wallet').prop('disabled', false);
                    var message = '<div class="updated"><p>' + response.data.message + '</p></div>';
                    $('.append_message').after(message);
                    // Get the current URL
                    var currentUrl = window.location.href;

                    // Remove any query parameters
                    var baseUrl = currentUrl.split('&')[0];
                    window.location.href = baseUrl;
                } else {
                    var message = '<div class="updated"><p>' + response.data.message + '</p></div>';
                    $('.append_message').after(message);
                   
                }
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                console.log('Error: ' + error);
            }
        });

    }
    


    $('#wm_manual_add_wallet').on('click', function() {

        var reciver_email = $('#wallet-reciver_email').val();
        var sendr_email = $('#wallet-sender_email').val();
        var pay_amount = $('#wallet-transaction-amount').val();
        var pay_note = $('#wallet-note').val();
        var end_date = $('#end_date').val();
        var statueses = 'Active';


        if(pay_amount===''){
            $('#amount_message').show();
        }
        else{
            $('#wm_manual_add_wallet').prop('disabled', true);
            $('#amount_message').hide();
            var transaction_type = $('#wallet-action').val();
            if(transaction_type ==='Credit'){
                var transaction_reference = 'Manually wallet credit by admin';
                var transaction_action = 'Wallet Credit';    
            }
            else{
            var transaction_reference = 'Manually wallet debit by admin';
            var transaction_action = 'Wallet Debit';    
            }
         wallet_transfer(reciver_email, sendr_email, pay_amount,transaction_type,transaction_action, transaction_reference, pay_note, end_date, statueses );
        }
        
    });

   function update_recharge(max_balance_en_dis_option, recharge_id, recharge_amount, recharge_action){


        $.ajax({
            url:ajaxurl,
            type: 'POST',
            data: {
                action: 'create_recharge_admin',
                nonce: php_var.nonce,
                recharge_id: recharge_id,
                max_balance_en_dis_option: max_balance_en_dis_option,
                recharge_amount: recharge_amount,
                recharge_action: recharge_action
            },
            success: function(response) {
                if (response.success) {
                    $('#recharge_wallet').prop('disabled', false);
                    var message = '<div class="updated"><p>' + response.data.message + '</p></div>';
                    $('.append_message').after(message);
                    setTimeout(function() {
                        location.reload()
                    }, 1000);
                } else {
                    var message = '<div class="updated"><p>' + response.data.message + '</p></div>';
                    $('.append_message').after(message);
                }
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                console.log('Error: ' + error);
            }
        });

    }

    $('#recharge_wallet').on('click', function() {
        
        var max_balance_en_dis_option = $('#max_balance_en_dis_option').is(':checked') ? 'yes' : 'no';
        var recharge_id = $('#recharge_id').val();
        var recharge_amount = $('#recharge_amount').val();
        var recharge_action = $('#recharge_action').val();

        if(recharge_amount===''){
            $('#recharge_amount_message').show();
        }
        else{
            $('#recharge_wallet').prop('disabled', true);
            $('#recharge_amount_message').hide();
            update_recharge(max_balance_en_dis_option, recharge_id, recharge_amount, recharge_action);
        }
        
    });

    $('#email_template_select').change(function() {
        
        var selectedOption = $(this).val();
        if (selectedOption === 'otp') {
            $('#otp_class').show();
            $('#recharge_class').hide();
            $('#cashback_class').hide();
            $('#transfer_class').hide();
            $('#byadmin_class').hide();
            $('#payment_class').hide();
        } 
        else if (selectedOption === 'recharge'){
            $('#otp_class').hide();
            $('#recharge_class').show();
            $('#cashback_class').hide();
            $('#transfer_class').hide();
            $('#byadmin_class').hide();
            $('#payment_class').hide();
        }
        else if (selectedOption === 'cashback'){
            $('#otp_class').hide();
            $('#recharge_class').hide();
            $('#cashback_class').show();
            $('#transfer_class').hide();
            $('#byadmin_class').hide();
            $('#payment_class').hide();
        }
        else if (selectedOption === 'transfer'){
            $('#otp_class').hide();
            $('#recharge_class').hide();
            $('#cashback_class').hide();
            $('#transfer_class').show();
            $('#byadmin_class').hide();
            $('#payment_class').hide();
        }
        else if (selectedOption === 'payment'){
            $('#otp_class').hide();
            $('#recharge_class').hide();
            $('#cashback_class').hide();
            $('#transfer_class').hide();
            $('#byadmin_class').hide();
            $('#payment_class').show();
        }
        else{
            $('#otp_class').hide();
            $('#recharge_class').hide();
            $('#cashback_class').hide();
            $('#transfer_class').hide();
            $('#byadmin_class').show();
            $('#payment_class').hide();
            
        }
    });

    var selectedOptionPageload = $('#email_template_select').val();
    if (selectedOptionPageload === 'otp') {
        $('#otp_class').show();
        $('#recharge_class').hide();
        $('#cashback_class').hide();
        $('#transfer_class').hide();
        $('#byadmin_class').hide();
        $('#payment_class').hide();
    } 
    else if (selectedOptionPageload === 'recharge'){
        $('#otp_class').hide();
        $('#recharge_class').show();
        $('#cashback_class').hide();
        $('#transfer_class').hide();
        $('#byadmin_class').hide();
        $('#payment_class').hide();
    }
    else if (selectedOptionPageload === 'cashback'){
        $('#otp_class').hide();
        $('#recharge_class').hide();
        $('#cashback_class').show();
        $('#transfer_class').hide();
        $('#byadmin_class').hide();
        $('#payment_class').hide();
    }
    else if (selectedOptionPageload === 'transfer'){
        $('#otp_class').hide();
        $('#recharge_class').hide();
        $('#cashback_class').hide();
        $('#transfer_class').show();
        $('#byadmin_class').hide();
        $('#payment_class').hide();
    }
    else if (selectedOptionPageload === 'payment'){
        $('#otp_class').hide();
        $('#recharge_class').hide();
        $('#cashback_class').hide();
        $('#transfer_class').hide();
        $('#byadmin_class').hide();
        $('#payment_class').show();
    }
    else{
        $('#otp_class').hide();
        $('#recharge_class').hide();
        $('#cashback_class').hide();
        $('#transfer_class').hide();
        $('#byadmin_class').show();
        $('#payment_class').hide();
    }

    $('#wallet-action').change(function() {
        
        var selected_option = $(this).val();
        if (selected_option === 'Debit') {
            $('.end_date_tr').hide();
        }
        else{
            $('.end_date_tr').show();
        }

     });
    $('td.column-status').each(function(){
        var statusText = $(this).text().trim();
        if(statusText === 'Active') {
            $(this).addClass('active-status');
        } else if(statusText === 'Expired') {
            $(this).addClass('expired-status');
        }
    });

    
    

});

document.addEventListener('DOMContentLoaded', function() {
    var filterType = document.getElementById('filter_wallet_balance');
    var walletMin = document.getElementById('wallet_min');
    var walletMax = document.getElementById('wallet_max');
    var filters_button = document.getElementById('wallet-query-submit');

    filterType.addEventListener('change', function() {
        if (this.value == 'between') {
            walletMin.style.display = 'inline';
            walletMax.style.display = 'inline';
            filters_button.disabled = false;
        } else if (this.value == 'less' || this.value == 'more') {
            walletMin.style.display = 'inline';
            walletMax.style.display = 'none';
            filters_button.disabled = false;
        } else {
            walletMin.style.display = 'none';
            walletMax.style.display = 'none';
            filters_button.disabled = true;
        }
    });

    // Trigger change event to set initial state
    filterType.dispatchEvent(new Event('change'));
});