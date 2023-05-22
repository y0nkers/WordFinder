$(document).ready(function () {
    let registerModal = new bootstrap.Modal(document.getElementById("registerModal")),
        loginModal = new bootstrap.Modal(document.getElementById("loginModal")),
        forgetModal = new bootstrap.Modal(document.getElementById("forgetModal"));

    // Форма регистрации
    $("#goRegister").click(function () {
        registerModal.show();
        loginModal.hide();
    });

    // Форма авторизации
    $("#navbar-login, #goLogin").click(function () {
        registerModal.hide();
        loginModal.show();
    });

    // Форма восстановления пароля
    $("#goForget1, #goForget2").click(function () {
       registerModal.hide();
       loginModal.hide();
       forgetModal.show();
    });

    // Регистрация
    $("#registerForm").submit(function (event) {
        event.preventDefault();

        let login = $('input[name="login_register"]').val(),
            email = $('input[name="email_register"]').val(),
            password = $('input[name="password_register"]').val(),
            password_confirm = $('input[name="password_confirm_register"]').val();

        let data = new FormData();
        data.append('type', "register");
        data.append('login', login);
        data.append('email', email);
        data.append('password', password);
        data.append('password_confirm', password_confirm);

        $.ajax({
            url: '/core/account.php',
            type: 'POST',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            data: data,
            success: function (response) {
                if (response.status === false) {
                    alert(response.message);
                } else {
                    alert("Регистрация прошла успешно!");
                    window.location.reload();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    // Авторизация
    $("#loginForm").submit(function (event) {
        event.preventDefault();

        let login = $('input[name="login"]').val(),
            password = $('input[name="password"]').val();

        let data = new FormData();
        data.append('type', "login");
        data.append('login', login);
        data.append('password', password);

        $.ajax({
            url: '/core/account.php',
            type: 'POST',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            data: data,
            success: function (response) {
                if (response.status === false) {
                    alert(response.message);
                } else {
                    document.location.href = '/profile.php';
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    // Восстановление пароля
    $("#forgetForm").submit(function (event) {
        event.preventDefault();

        let email = $('input[name="email_forget"]').val();

        let data = new FormData();
        data.append('type', "forget");
        data.append('email', email);

        $.ajax({
            url: '/core/account.php',
            type: 'POST',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            data: data,
            success: function (response) {
                if (response.status === false) {
                    alert(response.message);
                } else {
                    alert("Ссылка для восстановления пароля отправлена на указанный адрес");
                    forgetModal.hide();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    // Новый пароль
    $("#passwordForm").submit(function (event) {
        event.preventDefault();

        let password = $('input[name="newpassword"]').val();

        let data = new FormData();
        data.append('type', "newpassword");
        data.append('password', password);

        $.ajax({
            url: '/core/account.php',
            type: 'POST',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            data: data,
            success: function (response) {
                if (response.status === false) {
                    alert(response.message);
                } else {
                    alert("Пароль успешно обновлён!");
                    window.location.reload();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });
});