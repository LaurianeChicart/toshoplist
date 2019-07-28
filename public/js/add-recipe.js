const addRecipeForm = {

    collection: '',
    addBtnDesc: '',
    removeBtnDesc: '',
    addBtn: '',
    form: '',
    nameId: '',
    portionsNbId: '',
    imageId: '',
    ingredientPrefixe: '',
    quantitySuffix: '',
    nameSuffix: '',
    departmentSuffix: '',
    idSuffix: '',
    listIngredients: '',
    counterIngredients: 0,
    $availableTags: '',
    portionsNb: '',
    name: '',
    image: '',

    constructor(collection, addBtnDesc, removeBtnDesc, addBtn, form, nameId, portionsNbId, imageId, ingredientPrefixe, quantitySuffix, nameSuffix, departmentSuffix, idSuffix, listIngredients) {

        this.collection = collection;
        this.addBtnDesc = addBtnDesc;
        this.removeBtnDesc = removeBtnDesc;
        this.addBtn = addBtn;
        this.form = form;
        this.nameId = nameId;
        this.portionsNbId = portionsNbId;
        this.imageId = imageId;
        this.ingredientPrefixe = ingredientPrefixe;
        this.quantitySuffix = quantitySuffix;
        this.nameSuffix = nameSuffix;
        this.departmentSuffix = departmentSuffix;
        this.idSuffix = idSuffix;
        this.listIngredients = listIngredients;

        //symfony collection jquery plugin
        this.collection.collection({
            add: this.addBtnDesc,
            remove: this.removeBtnDesc,
            allow_up: false,
            allow_down: false,
            min: 1,
            max: 10,
            init_with_n_elements: 1,
            add_at_the_end: true,
        });
        //préparation de la liste d'ingrédient pour l'autocomplétion
        this.listIngredients = this.listIngredients.replace(/name/g, "label");
        this.listIngredients = this.listIngredients.replace(/id/g, "value");
        this.availableTags = JSON.parse(this.listIngredients);

        this.portionsNb = $("#" + this.portionsNbId);
        this.name = $("#" + this.nameId);
        this.image = document.getElementById(this.imageId);

        //activation de l'autocomplete
        this.activeAutocomplete(this.counterIngredients);

        //mise en place de vérification de saisie au blur sur chaque champ du formulaire
        this.name.change(this.checkName);
        this.portionsNb.change(this.checkPortionsNb);
        this.image.addEventListener("change", this.checkImage);

        let $quantity = $("#" + this.ingredientPrefixe + 0 + this.quantitySuffix);
        let $nameIngredient = $("#" + this.ingredientPrefixe + 0 + this.nameSuffix);
        let $department = $("#" + this.ingredientPrefixe + 0 + this.departmentSuffix);

        $quantity.blur(function () {
            addRecipeForm.checkQuantity(0);
        });
        $nameIngredient.blur(function () {
            addRecipeForm.checkNameIngredient(0);
        });
        $department.blur(function () {
            addRecipeForm.checkDepartment(0);
        });
        this.form.submit(this.checkForm);

        //à l'ajout d'un nouveau formulaire d'ingrédient, on lui applique l'autocomplete et la vérification de saisie au blur
        $(".recipe_recipeIngredients-collection-rescue-add").click(function () {
            addRecipeForm.counterIngredients++;
            setTimeout(function () {
                addRecipeForm.activeAutocomplete(addRecipeForm.counterIngredients);

                let $newQuantity = $("#" + addRecipeForm.ingredientPrefixe + addRecipeForm.counterIngredients + addRecipeForm.quantitySuffix);
                let $newNameIngredient = $("#" + addRecipeForm.ingredientPrefixe + addRecipeForm.counterIngredients + addRecipeForm.nameSuffix);
                let $newDepartment = $("#" + addRecipeForm.ingredientPrefixe + addRecipeForm.counterIngredients + addRecipeForm.departmentSuffix);

                $newQuantity.change(function () {
                    addRecipeForm.checkQuantity(addRecipeForm.counterIngredients);
                });
                $newNameIngredient.change(function () {
                    addRecipeForm.checkNameIngredient(addRecipeForm.counterIngredients);
                });
                $newDepartment.blur(function () {
                    addRecipeForm.checkDepartment(addRecipeForm.counterIngredients);
                });

            }, 500);
        });

    },

    // jquery ui interface autocomplete
    activeAutocomplete(i) {
        $("#" + this.ingredientPrefixe + this.counterIngredients + this.nameSuffix).blur(function () {
            addRecipeForm.ckeckListIngredients(addRecipeForm.counterIngredients);
        });
        $("#" + this.ingredientPrefixe + i + this.nameSuffix).autocomplete({
            minLength: 0,
            source: this.availableTags,
            select: function (event, ui) {
                addRecipeForm.fillFieldsAtAutocompletion(ui.item.label, ui.item.department.value, ui.item.value, i);

                return false;
            },
            open: function (event, ui) {
                $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix).removeAttr("disabled");
                $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix).val('');
                $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.idSuffix).val('');
            },
        });
    },

    ckeckListIngredients(i) {
        // au blur, on veut vérifier si la valeur du champ 'ingrédient' correspond à un élément du tableau JSON
        // on va comparer les valeurs en les mettant en minuscules et en enlevant les accents
        var aliment = addRecipeForm.removeAccents($.trim($("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.nameSuffix).val()).toLowerCase());
        $.each(addRecipeForm.availableTags, function (j, obj) {
            var nom = addRecipeForm.removeAccents($.trim(obj.label).toLowerCase());
            if (nom == aliment) {
                addRecipeForm.fillFieldsAtAutocompletion(obj.label, obj.department.value, obj.value, i);
            }
        });
    },

    removeAccents(str) {
        var accents = 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÕÖØòóôõöøÈÉÊËèéêëðÇçÐÌÍÎÏìíîïÙÚÛÜùúûüÑñŠšŸÿýŽž';
        var accentsOut = "AAAAAAaaaaaaOOOOOOOooooooEEEEeeeeeCcDIIIIiiiiUUUUuuuuNnSsYyyZz";
        str = str.split('');
        var strLen = str.length;
        var i, x;
        for (i = 0; i < strLen; i++) {
            if ((x = accents.indexOf(str[i])) != -1) {
                str[i] = accentsOut[x];
            }
        }
        return str.join('');
    },

    fillFieldsAtAutocompletion(label, department, value, i) {
        $("#" + this.ingredientPrefixe + i + this.nameSuffix).val(label);
        $("#" + this.ingredientPrefixe + i + this.departmentSuffix).val(department);
        $("#" + this.ingredientPrefixe + i + this.idSuffix).val(value);
        $("#" + this.ingredientPrefixe + i + this.departmentSuffix).attr("disabled", "disabled");

        addRecipeForm.deleteErrorMessageIngredient(this.ingredientPrefixe + i + this.departmentSuffix);

    },

    checkPortionsNb() {
        addRecipeForm.deleteErrorMessage(addRecipeForm.portionsNbId);
        let convertedPortions = addRecipeForm.convertToNumber($.trim(addRecipeForm.portionsNb.val()));
        addRecipeForm.portionsNb.val(convertedPortions);
        if (Number.isInteger(Number(addRecipeForm.portionsNb.val())) == false || addRecipeForm.portionsNb.val() < 1) {
            addRecipeForm.showErrorMessage(addRecipeForm.portionsNbId, " Le nombre de parts doit être un entier positif.");
            return false;
        }
    },

    checkName() {
        addRecipeForm.deleteErrorMessage(addRecipeForm.nameId);
        if ($.trim(addRecipeForm.name.val()).length < 6 || $.trim(addRecipeForm.name.val()) == '') {
            addRecipeForm.showErrorMessage(addRecipeForm.nameId, " Le nom doit comporter au moins 6 caractères.");
            return false;
        }
    },

    checkImage() {
        addRecipeForm.deleteErrorMessage(addRecipeForm.imageId);
        var allowedFileTypes = ["image/png", "image/jpeg"];
        if (addRecipeForm.image.files[0]) {
            if (addRecipeForm.image.files[0].size > 1000000 || allowedFileTypes.indexOf(addRecipeForm.image.files[0].type) <= -1) {
                addRecipeForm.showErrorMessage(addRecipeForm.imageId, " L'image doit être au format JPEG ou PNG et ne peut dépasser 1Mo.");
                return false;
            }
        }
    },
    checkQuantity(i) {
        let $quantity = $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.quantitySuffix);

        addRecipeForm.deleteErrorMessageIngredient(addRecipeForm.ingredientPrefixe + i + addRecipeForm.quantitySuffix);

        if ($.trim($quantity.val()) == '') {
            return false;
        }
        else {
            let convertedQuantity = addRecipeForm.convertToNumber($.trim($quantity.val()));
            $quantity.val(convertedQuantity);
            if (isNaN(Number($quantity.val()))) {
                addRecipeForm.showErrorMessageIngredient(addRecipeForm.ingredientPrefixe + i + addRecipeForm.quantitySuffix, " Le nombre de parts doit être un nombre positif.");
                return false;
            }
        }
    },
    checkNameIngredient(i) {
        let $nameIngredient = $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.nameSuffix);
        addRecipeForm.deleteErrorMessageIngredient(addRecipeForm.ingredientPrefixe + i + addRecipeForm.nameSuffix);
        if (($.trim($nameIngredient.val())).length < 3 || ($.trim($nameIngredient.val())) == '') {
            addRecipeForm.showErrorMessageIngredient(addRecipeForm.ingredientPrefixe + i + addRecipeForm.nameSuffix, " Le nom doit comporter au moins 3 caractères.");
            return false;
        }
    },
    checkDepartment(i) {
        let $department = $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix);
        addRecipeForm.deleteErrorMessageIngredient(addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix);
        if ($department.val() == '') {
            addRecipeForm.showErrorMessageIngredient(addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix, " Un rayon doit être sélectionné pour chaque ingrédient.");
            return false;
        }
    },

    checkForm(e) {

        for (i = 0; i <= addRecipeForm.counterIngredients; i++) {
            let $department = $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix);
            let $id = $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.idSuffix);

            addRecipeForm.deleteErrorMessageIngredient(addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix);

            if (addRecipeForm.checkQuantity(i) == false) {
                e.preventDefault();
            }
            if (addRecipeForm.checkNameIngredient(i) == false) {
                e.preventDefault();
            }
            //si l'id de l'ingrédient n'est pas défini, il faut obligatoirement que le rayon le soit
            if ($id.val() == '' && $department.val() == '') {
                e.preventDefault();
                addRecipeForm.showErrorMessageIngredient(addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix, " Un rayon doit être sélectionné pour chaque ingrédient.");
            }

        }
        if (addRecipeForm.checkName() == false) {
            e.preventDefault();
        }
        if (addRecipeForm.checkPortionsNb() == false) {
            e.preventDefault();
        }
        if ('files' in addRecipeForm.image) {
            if (addRecipeForm.checkImage() == false) {
                e.preventDefault();
            }
        }
    },

    //gestion des messages d'erreur des champs généraux
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

    //gestion des messages d'erreur des champs de la collection d'ingrédients
    showErrorMessageIngredient(inputId, message) {
        var $ingredientsGroup = $("div.ingredient-collection");
        var $errorMessage = "<span class='invalid-feedback " + inputId + " d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> " + message + "</span></span></span>";
        $ingredientsGroup.before($errorMessage);
        var $input = $("#" + inputId);
        $input.addClass("is-invalid");
    },
    deleteErrorMessageIngredient(inputId) {
        var $invalidSpans = $("#recipe_recipeIngredients").prevAll("span." + inputId);
        var $input = $("#" + inputId);
        if ($invalidSpans.length > 0) {
            $invalidSpans.each(function () {
                $(this).remove();
            });
        }
        if ($input.hasClass("is-invalid")) {
            $input.removeClass("is-invalid");
        }
    },

    convertToNumber(value) {
        //passer de 0,5 à 0.5
        if (value.includes(',')) {
            $result = value.replace(',', '.');
            return $result;
        }
        //passer de 1/2 à 0.5
        else if (value.includes('/')) {
            var $fraction = value.split("/");
            var UpNumber = $fraction[0];
            var bottomNimber = $fraction[1];
            var $result = UpNumber / bottomNimber;
            return $result;
        }
        else {
            return value;
        }
    }

}












