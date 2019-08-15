const planningForm = {
    form: "",
    formSubmit: "",
    dayLabels: "",
    dayCollection: "",
    mealCollection: "",
    days: "",
    plannedMealRecipe: "",
    plannedMealPortion: "",
    editMode: "",
    modalSubmit: "",
    confirmSubmit: "",

    constructor(form, formSubmit, dayLabels, dayCollection, mealCollection, days, plannedMealRecipe, plannedMealPortion, editMode, modalSubmit, confirmSubmit) {
        this.form = form;
        this.formSubmit = formSubmit;
        this.dayLabels = dayLabels;
        this.dayCollection = dayCollection;
        this.mealCollection = mealCollection;
        this.days = days;
        this.plannedMealRecipe = plannedMealRecipe;
        this.plannedMealPortion = plannedMealPortion;
        this.editMode = editMode;
        this.modalSubmit = modalSubmit;
        this.confirmSubmit = confirmSubmit;

        this.labelsCollectionToDates();

        dayCollection.collection({
            min: 1,
            prefix: 'parent',
            allow_add: false,
            allow_remove: false,
            allow_up: false,
            allow_down: false,
            children: [
                {
                    selector: mealCollection,
                    add: '<a class="btn btn-default btn-secondary text-white mb-3" href="#" title="Ajouter un repas"><span class="fas fa-plus"></span> Ajouter un repas</a>',
                    remove: '<a class="btn btn-default btn-dark" href="#" title="Supprimer ce repas"><span class="fas fa-times"></span></a>',
                    allow_up: false,
                    allow_down: false,
                    min: 0,
                    max: 15,
                    add_at_the_end: true
                }
            ]
        });

        formSubmit.on("click", this.checkForm);
    },

    checkForm(e) {
        let n = 0;
        if ($(planningForm.form + " span.invalid-feedback").length > 0) {
            $(planningForm.form + " span.invalid-feedback").each(function () {
                $(this).remove();
            });
        }
        if ($(".is-invalid").length > 0) {
            $(".is-invalid").each(function () {
                $(this).removeClass("is-invalid");
            });
        }
        if ($(planningForm.plannedMealRecipe).length < 1) {
            e.preventDefault();
            let $errorMessage = "<span class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> Le planning ne peut être validé car il est vide !</span></span></span>";
            $(planningForm.form).prepend($errorMessage);
            window.scrollTo(0, 0);
            n++;
        }
        $(planningForm.plannedMealRecipe).each(function () {
            if (($.trim($(this).val())) == "") {
                e.preventDefault();
                let $errorMessage = "<span class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> Chaque repas doit désigner une recette</span></span></span>";
                $(planningForm.form).prepend($errorMessage);
                $(this).addClass("is-invalid");
                window.scrollTo(0, 0);
                n++;
            }
        });
        $(planningForm.plannedMealPortion).each(function () {
            if (($.trim($(this).val())) == "" || isNaN(this.value)) {
                e.preventDefault();
                let $errorMessage = "<span class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> Chaque repas doit indiquer le nombre de parts (nombre entier)</span></span></span>";
                $(planningForm.form).prepend($errorMessage);
                $(this).addClass("is-invalid");
                window.scrollTo(0, 0);
                n++;
            }
        });
        if (planningForm.editMode == 1 && n == 0) {
            e.preventDefault();
            planningForm.modalSubmit.modal();
            planningForm.confirmSubmit.click(function () {
                $(planningForm.form).submit();
            });
        }

    },
    labelsCollectionToDates() {
        var formatedDates = [];

        function fromTimeStampToDate(timestamp) {
            let date = new Date(timestamp * 1000);
            let dayWeek;
            let day = date.getDate();
            let month;

            switch (date.getDay()) {
                case 0:
                    dayWeek = "dimanche";
                    break;
                case 1:
                    dayWeek = "lundi";
                    break;
                case 2:
                    dayWeek = "mardi";
                    break;
                case 3:
                    dayWeek = "mercredi";
                    break;
                case 4:
                    dayWeek = "jeudi";
                    break;
                case 5:
                    dayWeek = "vendredi";
                    break;
                case 6:
                    dayWeek = "samedi";
                    break;
            };
            switch (date.getDate()) {
                case 1:
                    day = "1er";
                    break;
            }
            switch (date.getMonth()) {
                case 0:
                    month = "janvier";
                    break;
                case 1:
                    month = "février";
                    break;
                case 2:
                    month = "mars";
                    break;
                case 3:
                    month = "avril";
                    break;
                case 4:
                    month = "mai";
                    break;
                case 5:
                    month = "juin";
                    break;
                case 6:
                    month = "juillet";
                    break;
                case 7:
                    month = "août";
                    break;
                case 8:
                    month = "septembre";
                    break;
                case 9:
                    month = "octobre";
                    break;
                case 10:
                    month = "novembre";
                    break;
                case 11:
                    month = "décembre";
                    break;
            };

            formatedDates.push("Repas du " + dayWeek + " " + day + " " + month);
        }
        this.days.forEach(fromTimeStampToDate);

        for (let i = 0; i < this.dayLabels.length; i++) {
            this.dayLabels[i].textContent = formatedDates[i];
        }
    }
}







