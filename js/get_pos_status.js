jQuery(document).ready(function ($) {
    let params = new URL(document.location).searchParams

    // if (!params.get('reloaded')) {
    //     get_pos_status('onload');
    // }
    
    $('#gen_connote').on('click', function (e) {
        $('#bulk-action-selector-top').prop('selectedIndex',0);
       
    });
    $('#get_pos_status').on('click', function (e) {
       
        e.preventDefault();
        showSpinner();
        get_pos_status('button');
    });
       function get_pos_status(source = '') {
        let post_ids = [];
        const id_checkbox = "input:checkbox[name='post[]']"

        if (source == 'button') {
            $(id_checkbox + ':checked').each(function () {
                post_ids.push(this.value);
            });
        }
        else if (source == 'onload') {
            $(id_checkbox).each(function () {
                post_ids.push(this.value);
            });
        }

        // console.log(post_ids);
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_pos_status',
                post_ids: post_ids,
                source: source,
                nonce: ajax_var.nonce,
            },
            success: function (response) {
                console.log(response);
                console.log(response);
                alert(response);
                if (source == 'onload' && !jQuery.isEmptyObject(response.data[2])) {
                    $.each(response.data[2], function (id, val) {
                        const id_status = '#post-' + id + ' > .column-pl_status'
                        $(id_status).html(val);
                    });
                }
                else if (source == 'button') {
                    params.delete("acti")
                    params.delete("msg")
                    params.delete("reloaded")
                    params.append("reloaded", "1")

                    if (response.success) {
                        params.append("acti", "success")
                        params.append("msg", response.data[1])
                    }
                    else {
                        params.append("acti", "err2")
                        params.append("msg", response.data[1])
                    }
                    newParams = params.toString()
                    hideSpinner()
                   window.location.href = location.protocol + '//' + location.host + location.pathname + '?' + newParams
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                hideSpinner()
            }
        });
    }

    function showSpinner() {
        $('.pos-status-spinner-container').fadeIn();
    }

    function hideSpinner() {
        $('.pos-status-spinner-container').fadeOut('slow');
    }
});