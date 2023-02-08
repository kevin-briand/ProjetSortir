
$("#inscription_*").click(function (event) {
    console.log(event);
/*
    if (email !== emailConfirm) {

        $('#noEmailMatch').remove();
        $(emailMatchField).css('background-color', redBgColor);
        let matchHtml = '<small id=' + emailMatchErrorId + '" class="red-text" >Email fields must match</small>';
        $(emailMatchField).after(matchHtml);

    }
    if (email === emailConfirm) {
        let data = {"email" : email};
        // if the email fields match make sure the email does not exist in the system
        let checkEmailUrl = Routing.generate( 'email_exists');
        let ezAjax = new EZAjax(emailExistsError, emailExistsSuccess, checkEmailUrl, data);
        console.log('the route is ' + checkEmailUrl);
        ezAjax.performRequest();
        $('#noEmailMatch').remove();
        $(emailMatchField).css('background-color', whiteBgColor);
    }*/
});

function inscription(id) {
    console.log("inscription", id);



    let data = {"id" : id};
    let checkEmailUrl = Routing.generate( 'inscription/');
    let ezAjax = new EZAjax(emailExistsError, emailExistsSuccess, checkEmailUrl, data);
    console.log('the route is ' + checkEmailUrl);
    ezAjax.performRequest();
}