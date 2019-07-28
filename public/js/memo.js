const memo = {

    // le formulaire
    addForm: '',
    // l'input d'ajout
    addInput: '',
    // liste des items
    listInput: '',
    // bouton pour supprimer un élément
    deleteBtns: '',
    // bouton pour vider la liste
    resetBtn: '',

    constructor(form, input, list, xBtns, btn) {
        this.addForm = form;
        this.addInput = input;
        this.listInput = list;
        this.deleteBtns = xBtns;
        this.resetBtn = btn;

        // quand on supprime un élément de la liste
        this.deleteBtns.each(function () {
            $(this).on("click", memo.deleteItem)
        });

        // quand on vide la liste
        this.resetBtn.on("click", memo.resetList);

        // quand on valide le formulaire
        this.addForm.submit(memo.addItem);
    },

    deleteItem(e) {
        e.preventDefault();
        let selection = $(this);
        let url = selection.attr('href');
        $.ajax({
            url: url,
            dataType: 'json',
            success: selection.parents('li').remove()
        });
    },

    resetList(e) {
        e.preventDefault();
        let selection = $(this);
        let url = selection.attr('href');

        $.ajax({
            url: url,
            success: memo.listInput.empty()

        });
    },

    addItem(e) {
        e.preventDefault();
        let selection = $(this);
        let url = selection.attr('action');
        let data = selection.serialize();

        $.ajax({
            url: url,
            type: 'post',
            data: data,
            dataType: 'json',

            success: function (reponse) {
                memo.listInput.append("<li><p class='item'>" + reponse.item + "<span><a class='btn btn-secondary btn-sm close' href=' /moncompte/supprimer-element-memo/" + reponse.id + "'><span class='fas fa-times'></span></a></span></p></li>");
                memo.addInput.val('');
            },
            complete: function () {
                memo.deleteBtns = $(".close");
                memo.deleteBtns.click(memo.deleteItem);
            }
        });

    }

}







