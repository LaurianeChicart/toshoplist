const registrationForm = {
    form: "",
    email: "",
    password: "",
    confirmPassword: "",
    targetsAnimation: "",
    classAnimation: "",

    constructor(form, email, password, confirmPassword, targetsAnimations, classAnimation) {

        this.form = form;
        this.email = email;
        this.password = password;
        this.confirmPassword = confirmPassword;
        this.targetsAnimation = targetsAnimations;
        this.classAnimation = classAnimation;

        // transition d'apparition au scroll
        //ratio de la taille de l'objet qui doit apparaître dans la fenêtre pour que celui-ci soit considéré comme visible
        const threshold = .3
        const options = {
            root: null, //référence = fenêtre
            rootMargin: "0px", //pas de marge sur la fenêtre
            threshold
        }
        const handleIntersect = function (entries, observer) {
            // lorsqu'il y a intersection, 
            entries.forEach(function (entry) {
                // si plus d'1/3 d'un élément concerné apparaît dans la fenêtre
                if (entry.intersectionRatio > threshold) {
                    // on lui applique la classe qui déclanche la transition
                    entry.target.classList.add(registrationForm.classAnimation);
                    // pour que l'animation ne se produise qu'à la 1ère apparition, on retire le gestionnaire d'événements
                    observer.unobserve(entry.target);
                }
            })
        }
        window.addEventListener("DOMContentLoaded", function () {
            // classe qui observe l'intersection selon les options définies et qui appelle la function handleIntersect à l'intersection
            const observer = new IntersectionObserver(handleIntersect, options);
            const targets = document.querySelectorAll(registrationForm.targetsAnimation);
            // on guette l'intersection pour tous les éléments souhaités
            targets.forEach(function (target) {
                observer.observe(target);
            })
        })

        this.form.on("submit", registrationForm.checkForm);
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
        var $errorMessage = "<span class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message text-white'> " + message + "</span></span></span>";
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
