const memo = {

    addForm: "",
    addInput: "",
    listInput: "",
    deleteBtns: "",
    resetBtn: "",

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
        let url = selection.attr("href");
        $.ajax({
            url: url,
            dataType: "json",
            success: selection.parents("li").remove()
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
        let url = selection.attr("action");
        let data = selection.serialize();
        if ($.trim(memo.addInput.val()) != "") {
            $.ajax({
                url: url,
                type: "post",
                data: data,
                dataType: "json",

                success: function (reponse) {
                    memo.listInput.append("<li class='bg-light p-1 mb-2'><p class='item'>" + reponse.item + "<span><a class='btn btn-dark btn-sm delete float-right' href=' /moncompte/supprimer-element-memo/" + reponse.id + "'><span class='fas fa-times'></span></a></span></p></li>");
                    memo.addInput.val("");
                },
                complete: function () {
                    memo.deleteBtns = $(".delete");
                    memo.deleteBtns.click(memo.deleteItem);
                }
            });
        }

    }

}







