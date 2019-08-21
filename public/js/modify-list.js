
const listForm = {
    departmentCollection: "",
    departmentPosition: "",
    itemCollection: "",
    itemPosition: "",
    departmentLabels: "",
    departmentNames: "",
    form: "",
    itemName: "",
    itemInitialQuantities: "",

    constructor(departmentCollection, departmentPosition, itemCollection, itemPosition, departmentLabels, departmentNames, form, itemName, itemInitialQuantities) {
        this.departmentCollection = departmentCollection;
        this.departmentPosition = departmentPosition;
        this.itemCollection = itemCollection;
        this.itemPosition = itemPosition;
        this.departmentLabels = departmentLabels;
        this.departmentNames = departmentNames;
        this.form = form;
        this.itemName = itemName;
        this.itemInitialQuantities = itemInitialQuantities;

        //mise en place des noms de rayon
        this.departmentLabels.each(function () {
            for (let i = 0; i < departmentLabels.length; i++) {
                if ($(this).text() == i) {
                    $(this).text(departmentNames[i]);
                }
            }
        });

        this.departmentCollection.collection({
            prefix: "parent",
            allow_add: false,
            allow_remove: false,
            allow_up: true,
            allow_down: true,
            up: '<a href="#" class="btn btn btn-outline-light" title="Monter ce rayon"><span class="fas fa-arrow-up"></span></a>',
            down: '<a href="#" class="btn btn btn-outline-light" title="Descendre ce rayon"><span class="fas fa-arrow-down"></span></a>',
            hide_useless_buttons: true,
            drag_drop: true,
            drag_drop_options: {
                placeholder: "ui-state-highlight",
                opacity: 0.8,
                //containment: "parent"
            },
            position_field_selector: this.departmentPosition,
            children: [
                {
                    selector: this.itemCollection,
                    allow_add: true,
                    allow_remove: true,
                    allow_up: true,
                    allow_down: true,
                    add: '<a class="btn btn-secondary text-white mb-3" href="#" title="Ajouter un produit"><span class="fas fa-plus"></span> Ajouter un produit</a>',
                    remove: '<a class="btn btn-dark" href="#" title="Supprimer ce produit"><span class="fas fa-times"></span></a>',
                    up: '<a href="#" class="btn btn-dark" title="Monter ce produit"><span class="fas fa-arrow-up"></span></a>',
                    down: '<a href="#" class="btn btn-dark" title="Descendre ce produit"><span class="fas fa-arrow-down"></span></a>',
                    min: 0,
                    add_at_the_end: true,
                    hide_useless_buttons: true,
                    drag_drop: true,
                    drag_drop_options: {
                        placeholder: "ui-state-highlight",
                        opacity: 0.8,
                        //containment: "parent"
                    },
                    position_field_selector: this.itemPosition
                }
            ]
        });

        $(this.form).submit(this.checkForm);
    },

    checkForm(e) {

        listForm.itemInitialQuantities.removeAttr("disabled");

        if ($(listForm.form + " span.invalid-feedback").length > 0) {
            $(listForm.form + " span.invalid-feedback").each(function () {
                $(this).remove();
            })
        }
        if ($(".is-invalid").length > 0) {
            $(".is-invalid").each(function () {
                $(this).removeClass("is-invalid");
            })
        }
        if (listForm.itemName.length < 1) {
            e.preventDefault();
            listForm.itemInitialQuantities.attr("disabled", true);
            let $errorMessage = "<span class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> Votre liste ne peut être validée car elle est vide</span></span></span><br>";
            $(listForm.form).prepend($errorMessage);
            window.scrollTo(0, 0);
        }
        listForm.itemName.each(function () {
            if (($.trim($(this).val())) == "") {
                e.preventDefault();
                listForm.itemInitialQuantities.attr("disabled", true);
                let $errorMessage = "<span class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> Tous les éléments de la liste doivent avoir un nom</span></span></span><br>";
                $(listForm.form).prepend($errorMessage);
                $(this).addClass("is-invalid");
                window.scrollTo(0, 0);
            }
        });
    }
}





