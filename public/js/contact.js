const contactForm = {
    form: "",
    email: "",
    subject: "",
    message: "",

    constructor(form, email, subject, message) {
        this.form = form;
        this.email = email;
        this.subject = subject;
        this.message = message;
        this.form.on("submit", contactForm.checkForm);
        $("#" + this.email).on("blur", contactForm.checkEmail);
        $("#" + this.subject).on("blur", contactForm.checkSubject);
        $("#" + this.message).on("blur", contactForm.checkMessage);
    },

    checkEmail() {
        contactForm.deleteErrorMessage(contactForm.email);
        var regexMail = /^[a-zA-Z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$/;
        let email = $("#" + contactForm.email);
        if (!regexMail.test($.trim(email.val()))) {
            contactForm.showErrorMessage(contactForm.email, "Adresse email invalide");
            return false;
        }
    },
    checkSubject() {
        contactForm.deleteErrorMessage(contactForm.subject);
        let subject = $("#" + contactForm.subject);
        if ($.trim(subject.val()) == "") {
            contactForm.showErrorMessage(contactForm.subject, "Veuillez indiquer un objet");
            return false;
        }
    },
    checkMessage() {
        contactForm.deleteErrorMessage(contactForm.message);
        let message = $("#" + contactForm.message);
        if ($.trim(message.val()) == "") {
            contactForm.showErrorMessage(contactForm.message, "Veuillez indiquer un message");
            return false;
        }
    },

    checkForm(e) {
        if ($("p.valid-feedback")) {
            $("p.valid-feedback").remove();
        }

        if (contactForm.checkEmail() == false || contactForm.checkSubject() == false || contactForm.checkMessage() == false) {
            e.preventDefault();
        }
        else {
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
                    $successMessage.prependTo(contactForm.form);
                    $("#" + contactForm.subject).val("");
                    $("#" + contactForm.message).val("");
                },
                error: function (reponse) {
                    let $noSuccessMessage = $("<p class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> Envoi impossible</span></span></p>");
                    $noSuccessMessage.prependTo(contactForm.form);
                },
            });
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