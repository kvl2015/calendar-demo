$(function() {
    //var usernameEl = $('#username');
    //var passwordEl = $('#password');

    $('#loginSubmit').click(function() {
        let username = $('#username').val();
        let password = $('#password').val();
        let token = $('input[name="_csrf_token"]').val();
        let url = $('#loginForm').attr('action');
        let data = JSON.stringify({username: username, password: password});


        var $this = inputs = {};
        // Send all form's inputs
        $.each($('#loginForm').find('input'), function (i, item) {
            var $item = $(item);
            inputs[$item.attr('name')] = $item.val();
        });

        // Send form into ajax
        $.ajax({
            url: $('#loginForm').attr('action'),
            type: 'POST',
            contentType: "application/json",
            data: JSON.stringify(inputs),
            dataType: "json",
            success: function (data) {
                location.reload();
            },
            error: function(data) {
                console.log('error');
            }
        });
    })
});
