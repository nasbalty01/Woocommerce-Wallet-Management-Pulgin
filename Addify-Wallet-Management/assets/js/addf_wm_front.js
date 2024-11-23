    jQuery(function ($) {

    // $('#user-table-custom').DataTable();
    $(document).on('change', '.variation_id' ,  show_cash_rule_message);
    show_cash_rule_message();

    function show_cash_rule_message(){

        if(! $('.variation_id').length ){
            return;
        }
    let variation_id = $('.variation_id').val() && $('.variation_id').val() >= 1 ? $('.variation_id').val() : $('input[name=product_id]').val();

    console.log(variation_id);

    $('.entry-summary .af-wallet-custom-price-message').hide();

    $('.entry-summary .custom-price-message-'+variation_id).show();
    
    }

    $('#add_to_wallet_button').on('click', function() {
       
        $(".message_wallet").remove();
        var walletAmount = parseFloat($('#wallet_amount').val());
        var walletRechargeAmount = parseFloat($('#wallet_recharge_amount').val());
        var wallet_payment_min_amount = parseFloat($('#wallet_payment_min_amount').val());
        var wallet_payment_max_wallet = parseFloat($('#wallet_payment_max_wallet').val());
        

        var data = {
            'action': 'add_to_wallet_cart',
            'nonce': php_var.nonce,
            'wallet_amount': walletAmount,
        };

        
        if (isNaN(walletAmount)) {
            var empty_message = php_var.wallet_add_trnslation.empty_message;
            $('.woocommerce-notices-wrapper').append('<div class="message_wallet woocommerce-error"><div>' + empty_message + '</div></div>');
        } else if (walletAmount < wallet_payment_min_amount) {
            var min_message = php_var.wallet_add_trnslation.min_message + php_var.currency_symbol+ ' ' + wallet_payment_min_amount;
            $('.woocommerce-notices-wrapper').append('<div class="message_wallet woocommerce-error"><div>' + min_message + '</div></div>');
        } else if (walletAmount > wallet_payment_max_wallet) {
            var max_message = php_var.wallet_add_trnslation.max_message + php_var.currency_symbol + ' ' + wallet_payment_max_wallet;
            $('.woocommerce-notices-wrapper').append('<div class="message_wallet woocommerce-error" ><div>' + max_message + '</div></div>');
        } else if (walletAmount > walletRechargeAmount) {
            var recharge_message = php_var.wallet_add_trnslation.recharge_message;
            $('.woocommerce-notices-wrapper').append('<div class="message_wallet woocommerce-error" ><div>' + recharge_message + '</div></div>');
        }
        else{
            // Add the product to the cart using AJAX
        $.ajax({
            url: php_var.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $('.woocommerce-notices-wrapper').append(`
                        <div class="message_wallet woocommerce-message" style="display: flex; justify-content: space-between;">
                            <div style="flex: 1;">
                                ${response.data.message}
                            </div>
                            <div style="flex: 1; text-align: right;">
                                <a href="${response.data.cart_url}"  class="button wc-forward">View Cart</a>
                            </div>
                        </div>
                    `);
                    
                // Hide the quantity input
                $('#quantity').hide();
                } else {
                    $('.woocommerce-notices-wrapper').append('<div class="message_wallet woocommerce-error"><div>' + response.data.message + '</div></div>');
                    // Hide the quantity input
                    $('#quantity').hide();
                
                }
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                console.log('Error: ' + error);
            }
        });
    }
    });

        // Check if the current URL contains 'customer-wallet'
        if (window.location.href.includes('customer-wallet')) {
            // Replace 'My Account' with 'My Wallet' in the page heading
            document.querySelector('h1.entry-title').textContent = 'My Wallet';
        }

        var timer
        function timmerr(){
            var otp_time = $('#wm_otp_otion').val();
            var otp_time_second = otp_time * 60;
            var seconds = otp_time_second; // Start the timer at 1 second
            timer = setInterval(function() {
                seconds--;
                $('#wm_wallet_timer_msg').html(seconds + ' Seconds');
                if (seconds <= 0) { // Change 60 to the desired duration
                    $('#wm_wallet_verify_otp').prop('disabled', true);
                    $('#wm_wallet_resend_otp').show();
                    clearInterval(timer);
                }
            }, 1000);
        }

            $('.wm_wallet_transfer_money').on('click', function() {
                
                var email = $('#wm_wallet_receiver').val();
                var current_user_email = $('#wm_current_user_email').val();
                var pay_amount = $('#wm_wallet_pay_amount').val();
                var pay_note = $('#wm_wallet_pay_note').val();
                var total_amount = $('#total_amount').val();

                $('#wm_wallet_timer_msg').html('');
                $('.wm_wallet-spin-loader').show();
               
        
                $.ajax({
                    url: php_var.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'send_otp',
                        nonce: php_var.nonce,
                        email: email,
                        current_user_email: current_user_email,
                        pay_amount:pay_amount,
                        total_amount: total_amount,
                        pay_note:pay_note

                    },
                    success: function(response) {
                        if (response.success) {
                            $('.wm_wallet-spin-loader').hide();
                            $('#wm_wallet_transfer_money').hide();
                            timmerr();
                            $('.wkwc_wallet_otp_input').show();
                            $('#wm_wallet_verify_otp').show();
                            $('.wm_wallet_otp_success_notice').show();
                            $('#wm_wallet_info_msg').html(response.data.message);
        
                            
                            
                        } else {
                            $('.wm_wallet-spin-loader').hide();
                            $('.wm_wallet_otp_success_notice').show();
                            $('#wm_wallet_timer_msg').html(response.data.message);
                           
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                        $('#wm_wallet-spin-loader').hide();
                        $('#wm_wallet_timer_msg').html('');
                    }
                });
            });
        
            $('#wm_wallet_verify_otp').on('click', function() {
               
                var otpp = $('#wm_wallet_transfer_otp').val();
                var reciver_email = $('#wm_wallet_receiver').val();
                var sendr_email = $('#wm_current_user_email').val();
                var pay_amount = $('#wm_wallet_pay_amount').val();
                var pay_note = $('#wm_wallet_pay_note').val();

                var emails = [reciver_email, sendr_email];
                var transaction_type = 'Transfor';
                var transaction_reference = 'Wallet transfer';
                var transaction_action =['Wallet Credit', 'Wallet Debit'];
                $('#wm_wallet_timer_msg').html('');
            
                $.ajax({
                    url: php_var.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'verify_otp',
                        nonce: php_var.nonce,
                        otpp: otpp
                    },
                    success: function(response) {
                        if (response.success) {
                            wallet_transfer(emails, reciver_email, sendr_email, pay_amount, transaction_type, transaction_action, transaction_reference, pay_note);
                            clearInterval(timer);
                            $('#wm_wallet_verify_otp').prop('disabled', true);
                            $('#wm_wallet_info_msg').html(response.data.message);
                            
                            // Add logic to create a new order in WordPress
                        } else {
                            clearInterval(timer);
                            $('#wm_wallet_timer_msg').html(response.data.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            });
        
            $('#wm_wallet_resend_otp').on('click', function() {
                
                $('#wm_wallet_verify_otp').prop('disabled', false);
            });
            
            function wallet_transfer(emails, reciver_email, sendr_email, pay_amount, transaction_type, transaction_action, transaction_reference, pay_note) {
                $.ajax({
                    url: php_var.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'create_order',
                        nonce: php_var.nonce,
                        emails: emails,
                        reciver_email: reciver_email,
                        sendr_email: sendr_email,
                        transaction_type: transaction_type,
                        transaction_action: transaction_action,
                        transaction_reference: transaction_reference,
                        pay_amount: pay_amount,
                        pay_note: pay_note
                    },
                    success: function(response) {
                        if (response) {
                            location.reload();
                            // alert('Orders created successfully.');
                            console.log(response);
                        } else {
                            $('#wm_wallet_info_msg').html(response.data.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error: ' + error);
                    }
                });
            }


            
            // $('#table-table-front').DataTable();

            
            // for pagination 

    // var rowsPerPage = 10;
    // var table = document.getElementById('table-table-front');
    // var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    // var currentPage = 1;
    // var totalPages = Math.ceil(rows.length / rowsPerPage);

    // function showPage(page) {
    //     for (var i = 0; i < rows.length; i++) {
    //         rows[i].style.display = 'none';
    //     }
    //     for (var i = (page - 1) * rowsPerPage; i < (page * rowsPerPage) && i < rows.length; i++) {
    //         rows[i].style.display = '';
    //     }
    //     document.getElementById('pagination').style.display = (rows.length > rowsPerPage) ? 'block' : 'none';
    // }

    // document.getElementById('prev-page').addEventListener('click', function () {
    //     if (currentPage > 1) {
    //         currentPage--;
    //         showPage(currentPage);
    //     }
    // });

    // document.getElementById('next-page').addEventListener('click', function () {
    //     if (currentPage < totalPages) {
    //         currentPage++;
    //         showPage(currentPage);
    //     }
    // });

    // showPage(currentPage);

    $('td.column-status').each(function(){
        var statusText = $(this).text().trim();
        if(statusText === 'Active') {
            $(this).addClass('active-status');
        } else if(statusText === 'Expired') {
            $(this).addClass('expired-status');
        }
    });    
    
});


   
        
  