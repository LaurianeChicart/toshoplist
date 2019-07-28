const registrationForm = {
    form: '',
    email: '',
    password: '',
    confirmPassword: '',

    constructor(form, email, password, confirmPassword) {
        this.form = form;
        this.email = email;
        this.password = password;
        this.confirmPassword = confirmPassword;
        form.on("submit", registrationForm.checkForm);
        $("#" + this.email).on("blur", registrationForm.checkEmail);
        $("#" + this.password).on("blur", registrationForm.checkPassword);
        $("#" + this.confirmPassword).on("blur", registrationForm.checkConfirmPassword);
    },

    checkEmail() {
        registrationForm.deleteErrorMessage(registrationForm.email);
        var regexMail = /^[a-zA-Z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$/;
        let email = document.getElementById(registrationForm.email);
        if (!regexMail.test($.trim(email.value))) {
            registrationForm.showErrorMessage(registrationForm.email, "Adresse email invalide");
            return false;
        }
    },
    checkPassword() {
        registrationForm.deleteErrorMessage(registrationForm.password);
        let password = document.getElementById(registrationForm.password);
        if (password.value.indexOf(" ") > 0) {
            registrationForm.showErrorMessage(registrationForm.password, "Le mot de passe ne peut pas contenir d'espace");
            return false;
        }
        if (password.value.length < 8) {
            registrationForm.showErrorMessage(registrationForm.password, "Le mot de passe doit avoir 8 caractères minimum");
            return false;
        }
    },
    checkConfirmPassword() {
        registrationForm.deleteErrorMessage(registrationForm.confirmPassword);
        let password = document.getElementById(registrationForm.password);
        let confirmPassword = document.getElementById(registrationForm.confirmPassword);
        if (confirmPassword.value != password.value) {
            registrationForm.showErrorMessage(registrationForm.confirmPassword, "Confirmation incorrecte");
            return false;
        }
    },

    checkForm(e) {
        if (registrationForm.checkEmail() == false) {
            e.preventDefault();
        }
        if (registrationForm.checkPassword() == false) {
            e.preventDefault();
        }
        if (registrationForm.checkConfirmPassword() == false) {
            e.preventDefault();
        }
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
