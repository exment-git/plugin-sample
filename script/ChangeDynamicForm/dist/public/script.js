$(function () {
    $(window).off('exment:form_loaded', setDynamicFormEvent).on('exment:form_loaded', setDynamicFormEvent);

    function setDynamicFormEvent(){
        setDynamicForm();
        // block_custom_value_formはデータ入力画面、custom_value_agencyは代理店テーブルの関連画面
        $('.block_custom_value_form' + '.custom_value_agency')
            .off('change.exment_change_agency', ".value_syubetu,.value_tantosha", setDynamicForm)
            .on('change.exment_change_agency', ".value_syubetu,.value_tantosha", setDynamicForm)
    }

    function setDynamicForm(){
        // 種別と担当者の値を取得します。
        const value_syubetu = $('.value_syubetu').val();
        const value_tantosha = $('.value_tantosha').val();

        // 種別が法人の場合だけ法人コードを表示します。
        if(value_syubetu && value_syubetu.indexOf('法人') < 0) {
            $('.value_corpcode').closest('.row').hide();
            $('.value_corpcode').val('');
        } else {
            $('.value_corpcode').closest('.row').show();
        }

        // 担当者が1（管理者）の場合だけインセンティブレートを表示します。
        if (value_tantosha == '1') {
            $('.value_Incentive').closest('.row').show();
        } else {
            $('.value_Incentive').closest('.row').hide();
            $('.value_Incentive').val('');
        }
    }
});
