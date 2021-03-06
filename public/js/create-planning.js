const datesForm = {
    form: "",
    startDate: "",
    stopDate: "",

    constructor(form, startDate, stopDate) {
        this.form = form;
        this.startDate = startDate;
        this.stopDate = stopDate;
        this.form.submit(this.checkForm);
    },
    checkForm(e) {
        if ($("form span.invalid-feedback").length > 0) {
            $("form span.invalid-feedback").each(function () {
                $(this).remove();
            })
        }
        if (datesForm.startDate.val() == "" || datesForm.stopDate.val() == "") {
            e.preventDefault();
            let $errorMessage = "<span class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> Les 2 champs doivent être renseignés</span></span></span>";
            datesForm.form.prepend($errorMessage);
        }
        else if (datesForm.stopDate.val() < datesForm.startDate.val()) {
            e.preventDefault();
            let $errorMessage = "<span class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> La date de fin ne peut être antérieure à la date de début</span></span></span>";
            datesForm.form.prepend($errorMessage);
        }
    }
}

