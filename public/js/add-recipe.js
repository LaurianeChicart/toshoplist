const addRecipeForm = {

    collection: "",
    addBtnDesc: "",
    removeBtnDesc: "",
    addBtn: "",
    removeBtns: "",
    form: "",
    nameId: "",
    portionsNbId: "",
    imageId: "",
    ingredientPrefixe: "",
    quantitySuffix: "",
    nameSuffix: "",
    departmentSuffix: "",
    idSuffix: "",
    listIngredients: "",
    counterIngredients: "",
    availableTags: "",
    portionsNb: "",
    name: "",
    image: "",

    constructor(collection, addBtnDesc, removeBtnDesc, addBtn, removeBtns, form, nameId, portionsNbId, imageId, ingredientPrefixe, quantitySuffix, nameSuffix, departmentSuffix, idSuffix, listIngredients) {

        this.collection = collection;
        this.addBtnDesc = addBtnDesc;
        this.removeBtnDesc = removeBtnDesc;
        this.addBtn = addBtn;
        this.removeBtns = removeBtns;
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
        this.counterIngredients = this.collection.children("div").length;
        // en cas de création de recette, sa valeur sera 0, en cas de modification, elle sera de 1 minimum
        // comme on souhaite se servir de this.counterIngredients comme index de la collection d'ingrédient, le 1er sera ingredient[0] 
        if (this.counterIngredients != 0) {
            this.counterIngredients--;
        }

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


        //mise en place de vérification de saisie au blur sur chaque champ du formulaire
        this.name.change(this.checkName);
        this.portionsNb.change(this.checkPortionsNb);
        this.image.addEventListener("change", this.checkImage);

        this.form.submit(this.checkForm);

        //mise en place de l'autocomplétion et la vérification au blur de tous les champs ingrédients existants à l'affichage du formulaire
        for (var k = 0; k <= this.counterIngredients; k++) {
            this.activeAutocomplete(k);

            if ($("#" + this.ingredientPrefixe + k + this.idSuffix).val() != "") {
                let id = $("#" + this.ingredientPrefixe + k + this.idSuffix).val();
                $.each(this.availableTags, function (j, obj) {
                    if (id == obj.value) {
                        if (obj.userIsNull == true) {
                            $("#" + addRecipeForm.ingredientPrefixe + k + addRecipeForm.departmentSuffix).val(obj.department.value);
                            $("#" + addRecipeForm.ingredientPrefixe + k + addRecipeForm.departmentSuffix).attr("disabled", "disabled");
                        }
                    }
                });
            }
        }

        //à l'ajout d'un nouveau formulaire d'ingrédient, on lui applique l'autocomplete et la vérification de saisie au blur
        $(".recipe_recipeIngredients-collection-rescue-add").click(function () {
            addRecipeForm.counterIngredients++;
            setTimeout(function () {
                addRecipeForm.activeAutocomplete(addRecipeForm.counterIngredients);
            }, 500);
        });

        //à la suppression d'un ingrédient, on met à jour le compteur et on supprime les messages d'erreur ingrédient
        let removeButtons = document.getElementsByClassName(this.removeBtns);
        if (removeButtons.length > 0) {
            for (let j = 0; j < removeButtons.length; j++) {
                removeButtons[j].addEventListener("click", addRecipeForm.updateRemoveBtns);
            }
        }
    },

    updateRemoveBtns() {
        var $invalidSpans = $("#recipe_recipeIngredients").prevAll("span.invalid-feedback");
        if ($invalidSpans.length > 0) {
            $invalidSpans.each(function () {
                $(this).remove();
            });
        }
    },

    // jquery ui interface autocomplete
    activeAutocomplete(i) {
        $("#" + this.ingredientPrefixe + i + this.nameSuffix).blur(function () {
            addRecipeForm.ckeckListIngredients(i);
        });
        //gestion des accents
        var normalize = function (term) {
            var accentMap = {
                "Œ": "OE", "OE": "Œ", "œ": "oe", "oe": "œ", "À": "A", "Á": "A", "Â": "A", "Ã": "A", "Ä": "A", "Å": "A", "à": "a", "á": "a", "â": "a", "ã": "a", "ä": "a", "å": "a", "Ò": "O", "Ó": "O", "Ô": "O", "Õ": "O", "Õ": "O", "Ö": "O", "Ø": "O", "ò": "o", "ó": "o", "ô": "o", "õ": "o", "ö": "o", "ø": "o", "È": "E", "É": "E", "Ê": "E", "Ë": "E", "è": "e", "é": "e", "ê": "e", "ë": "e", "ð": "e", "Ç": "C", "ç": "c", "Ð": "D", "Ì": "I", "Í": "I", "Î": "I", "Ï": "I", "ì": "i", "í": "i", "î": "i", "ï": "i", "Ù": "U", "Ú": "U", "Û": "U", "Ü": "U", "ù": "u", "ú": "u", "û": "u", "ü": "u", "Ñ": "N", "ñ": "n", "Š": "S", "š": "s", "Ÿ": "Y", "ÿ": "y", "ý": "y", "Ž": "Z", "ž": "z"
            };
            var ret = "";
            for (var i = 0; i < term.length; i++) {
                ret += accentMap[term.charAt(i)] || term.charAt(i);
            }
            return ret;
        };
        $("#" + this.ingredientPrefixe + i + this.nameSuffix).autocomplete({
            minLength: 1,
            source: function (request, response) {
                var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
                response($.grep(addRecipeForm.availableTags, function (value) {
                    value = value.label || value.value || value;
                    return matcher.test(value) || matcher.test(normalize(value));
                }));
            },
            focus: function (event, ui) {
                $("#" + this.ingredientPrefixe + i + this.nameSuffix).val(ui.item.label);
                return false;
            },
            select: function (event, ui) {
                addRecipeForm.fillFieldsAtAutocompletion(ui.item.label, ui.item.department.value, ui.item.value, i);
                return false;
            },
            open: function (event, ui) {
                $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix).removeAttr("disabled");
                $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix).val("");
                $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.idSuffix).val("");
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
        var accents = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÕÖØòóôõöøÈÉÊËèéêëðÇçÐÌÍÎÏìíîïÙÚÛÜùúûüÑñŠšŸÿýŽž";
        var accentsOut = "AAAAAAaaaaaaOOOOOOOooooooEEEEeeeeeCcDIIIIiiiiUUUUuuuuNnSsYyyZz";
        if (str == "oeuf") {
            return "œuf";
        }
        else {
            str = str.split("");
            var strLen = str.length;
            var i, x;
            for (i = 0; i < strLen; i++) {
                if ((x = accents.indexOf(str[i])) != -1) {
                    str[i] = accentsOut[x];
                }
            }
            return str.join("");
        }
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
        if ($.trim(addRecipeForm.name.val()).length < 6 || $.trim(addRecipeForm.name.val()) == "") {
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

        if ($.trim($quantity.val()) == "") {
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
        if (($.trim($nameIngredient.val())).length < 3 || ($.trim($nameIngredient.val())) == "") {
            addRecipeForm.showErrorMessageIngredient(addRecipeForm.ingredientPrefixe + i + addRecipeForm.nameSuffix, " Le nom doit comporter au moins 3 caractères.");
            return false;
        }
    },
    checkDepartment(i) {
        let $department = $("#" + addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix);
        addRecipeForm.deleteErrorMessageIngredient(addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix);
        if ($department.val() == "") {
            addRecipeForm.showErrorMessageIngredient(addRecipeForm.ingredientPrefixe + i + addRecipeForm.departmentSuffix, " Un rayon doit être sélectionné pour chaque ingrédient.");
            return false;
        }
    },

    checkForm(e) {

        if (addRecipeForm.checkName() == false) {
            e.preventDefault();
        }
        if (addRecipeForm.checkPortionsNb() == false) {
            e.preventDefault();
        }
        if ("files" in addRecipeForm.image) {
            if (addRecipeForm.checkImage() == false) {
                e.preventDefault();
            }
        }
        //les index de la collection ne sont pas forcément linéaires ex: 0, 1, 3 (en cas de suppression du 2 lors d'une modification de recette)
        //on cherche donc à récupérer les index existants à la validation du formulaire pour vérifier leurs valeurs d'inputs
        var listIndexIngredients = [];
        let allIngredients = $("input[id*='" + addRecipeForm.quantitySuffix + "']");

        let $invalidSpans = $("#recipe_recipeIngredients").prevAll("span.invalid-feedback");
        if ($invalidSpans.length > 0) {
            $invalidSpans.each(function () {
                $(this).remove();
            });
        }

        allIngredients.each(function () {
            let id = $(this).attr("id");
            let array = id.split("_");
            let index = array[2];
            listIndexIngredients.push(index);
        });

        for (var i = 0; i < listIndexIngredients.length; i++) {
            let $department = $("#" + addRecipeForm.ingredientPrefixe + listIndexIngredients[i] + addRecipeForm.departmentSuffix);

            addRecipeForm.deleteErrorMessageIngredient(addRecipeForm.ingredientPrefixe + listIndexIngredients[i] + addRecipeForm.departmentSuffix);

            if (addRecipeForm.checkQuantity(listIndexIngredients[i]) == false) {
                e.preventDefault();
            }
            if (addRecipeForm.checkNameIngredient(listIndexIngredients[i]) == false) {
                e.preventDefault();
            }
            $department.removeAttr("disabled");

            if (addRecipeForm.checkDepartment(listIndexIngredients[i]) == false) {
                e.preventDefault();
                $department.attr("disabled")

            }
        };
    },

    //gestion des messages d'erreur des champs généraux
    showErrorMessage(inputName, message) {
        let $label = $("label[for=" + inputName + "]");
        let $errorMessage = "<span class='invalid-feedback d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> " + message + "</span></span></span>";
        $label.append($errorMessage);
        let $input = $("input[id=" + inputName + "]");
        $input.addClass("is-invalid");
        window.scrollTo(0, 0);
    },
    deleteErrorMessage(inputName) {
        if ($("label[for=" + inputName + "] span.invalid-feedback").length > 0) {
            let $errorMessageSpan = $("label[for=" + inputName + "] span.invalid-feedback");
            $errorMessageSpan.remove();
            let $input = $("input[id=" + inputName + "]");
            $input.removeClass("is-invalid");
        }
    },

    //gestion des messages d'erreur des champs de la collection d'ingrédients
    showErrorMessageIngredient(inputId, message) {
        let $ingredientsGroup = $("div.ingredient-collection");
        let $errorMessage = "<span class='invalid-feedback " + inputId + " d-block'><span class='d-block'><span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span><span class='form-error-message'> " + message + "</span></span></span>";
        $ingredientsGroup.before($errorMessage);
        let $input = $("#" + inputId);
        $input.addClass("is-invalid");
        window.scrollTo(0, 0);
    },
    deleteErrorMessageIngredient(inputId) {
        let $invalidSpans = $("#recipe_recipeIngredients").prevAll("span." + inputId);
        let $input = $("#" + inputId);
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
        if (value.includes(",")) {
            $result = value.replace(",", ".");
            return $result;
        }
        //passer de 1/2 à 0.5
        else if (value.includes("/")) {
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












