$(function () {
    $(window).off('exment:dashboard_loaded', '.box-dashboard', {}, scriptTestDashboard).on('exment:dashboard_loaded', '.box-dashboard', {}, scriptTestDashboard);
    $(window).off('exment:loaded', scriptTest).on('exment:loaded', scriptTest);

    function scriptTestDashboard(ev){
        console.log(ev.target);
    }

    function scriptTest(){
        console.log('loaded test');
    }
});
