$(function () {
    $(window).off('exment:form_loaded', setReplaceZenHanEvent).on('exment:form_loaded', setReplaceZenHanEvent);

    function setReplaceZenHanEvent(){
        $('.block_custom_value_form').off('change.exment_replace_zen_han', "input[type='text']", replaceZenHan).on('change.exment_replace_zen_han', "input[type='text']", replaceZenHan)
    }

    function replaceZenHan(ev){
        var $target = $(ev.target);
        if(!$target || $target.length == 0){
            return;
        }
        
        var str = $target.val();
        if(!str){
            return;
        }
        
        
        str = str.replace(/[Ａ-Ｚａ-ｚ０-９]/g, function(s) {
            return String.fromCharCode(s.charCodeAt(0) - 65248);
        });
        $target.val(str);
    }
});
