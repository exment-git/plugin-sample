// script.js
$(function () {
    $(window).off('exment:first_loaded', setSystemTableChangeEvent).on('exment:first_loaded', setSystemTableChangeEvent);

    function setSystemTableChangeEvent(ev){
        $(document).on('change.select2', '.system_table_column_list_select_table', {}, (ev) => {
            let $table = $(ev.target).val();
            let url = $('.system_table_column_list_root_url').val() + '?table=' + $table;
            $.admin.redirect(url);
        });
    }
});