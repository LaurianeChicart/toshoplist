const userDatasForm = {
    form: "",
    email: "",
    currentPassword: "",
    newPassword: "",
    confirmNewPassword: "",

    constructor(form, email, currentPassword, newPassword, confirmNewPassword) {
        this.form = form;
        this.email = email;
        this.currentPassword = currentPassword;
        this.newPassword = newPassword;
        this.confirmNewPassword = confirmNewPassword;
        this.form.on("submit", userDatasForm.checkForm);
        $("#" + this.email).on("blur", userDatasForm.checkEmail);
        $("#" + this.newPassword).on("blur", userDatasForm.checkNewPassword);
        $("#" + this.confirmNewPassword).on("blur", userDatasForm.checkConfirmNewPassword);
    },

    checkEmail() {
        userDatasForm.deleteErrorMessage(userDatasForm.email);
        var regexMail = /^[a-zA-Z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$/;
        let email = $("#" + userDatasForm.email);
        if (!regexMail.test($.trim(email.val()))) {
            userDatasForm.showErrorMessage(userDatasForm.email, "Adresse email invalide");
            return false;
        }
    },
    checkNewPassword() {
        userDatasForm.deleteErrorMessage(userDatasForm.newPassword);
        let newPassword = $("#" + userDatasForm.newPassword);
        if (newPassword.val().indexOf(" ") > 0) {
            userDatasForm.showErrorMessage(userDatasForm.newPassword, "Le mot de passe ne peut pas contenir d'espace");
            return false;
        }
        if (newPassword.val().length < 8 && newPassword.val().length > 0) {
            userDatasForm.showErrorMessage(userDatasForm.newPassword, "Le mot de passe doit avoir 8 caractères minimum");
            return false;
        }
    },
    checkConfirmNewPassword() {
        userDatasForm.deleteErrorMessage(userDatasForm.confirmNewPassword);
        let newPassword = $("#" + userDatasForm.newPassword);
        let confirmNewPassword = $("#" + userDatasForm.confirmNewPassword);
        if (confirmNewPassword.val() != newPassword.val()) {
            userDatasForm.showErrorMessage(userDatasForm.confirmNewPassword, "Confirmation incorrecte");
            return false;
        }
    },

    checkForm(e) {
        if ($("p.valid-feedback")) {
            $("p.valid-feedback").remove();
        }

        if (userDatasForm.checkEmail() == false) {
            e.preventDefault();
        }
        let newPassword = $("#" + userDatasForm.newPassword);
        let confirmNewPassword = $("#" + userDatasForm.confirmNewPassword);
        if (newPassword.val() != "" && confirmNewPassword.val() != "") {
            if (userDatasForm.checkNewPassword() == false) {
                e.preventDefault();
            }
            if (userDatasForm.checkConfirmNewPassword() == false) {
                e.preventDefault();
            }
        }
        e.preventDefault();
        let selection = $(this);
        let url = selection.attr("action");
        let data = selection.serialize();

        $.ajax({
            url: url,
            type: "post",
            data: data,
            dataType: "json",
            success: function (reponse) {
                let $successMessage = $("<p class='valid-feedback d-block text-primary'><span class='d-block'><span class='form-primary-icon badge badge-primary text-uppercase'>Succès</span><span class='form-primary-message'> " + reponse.message + "</span></span></p>");
                $successMessage.prependTo(userDatasForm.form);
                $("#" + userDatasForm.currentPassword).val("");
                $("#" + userDatasForm.newPassword).val("");
                $("#" + userDatasForm.confirmNewPassword).val("");

            },
            error: function (reponse) {
                let $noSuccessMessage = $("<p class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'>Mot de passe incorrect</span></span></p>");
                $noSuccessMessage.prependTo(userDatasForm.form);
            },
        });

    },
    showErrorMessage(inputName, message) {
        var $label = $("label[for=" + inputName + "]");
        var $errorMessage = "<span class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> " + message + "</span></span></span>";
        $label.append($errorMessage);
        var $input = $("input[id=" + inputName + "]");
        $input.addClass("is-invalid");
    },
    deleteErrorMessage(inputName) {
        if ($("label[for=" + inputName + "] span.invalid-feedback").length > 0) {
            var $errorMessageSpan = $("label[for=" + inputName + "] span.invalid-feedback");
            $errorMessageSpan.remove();
            var $input = $("input[id=" + inputName + "]");
            $input.removeClass("is-invalid");
        }
    },
}
