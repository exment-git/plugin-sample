$(function () {
    $(window).off('exment:calendar_bind', scriptCalendarBind).on('exment:calendar_bind', scriptCalendarBind);

    function scriptCalendarBind(e, event){
        // 「お知らせ」テーブルでないと終了
        // 前半：テーブルビューのチェック、後半：ダッシュボードのチェック
        if(!$('.custom_value_information').length && !$('[data-target_table_name="information"]').length){
            return;
        }

        // 「重要度」が高い(4)以上の場合は強制的に赤背景に変更
        if(event.value && event.value.priority >= 4){
            event.color = 'red';
            event.textColor = 'white';
        }
        return event;
    }
});
