const shopList = {
    inputIngredients: "",
    //la partie textuelle de l'input à barrer au check
    itemDetails: "",

    constructor(inputIngredients, itemDetails) {
        this.inputIngredients = inputIngredients;
        this.itemDetails = itemDetails;

        this.inputIngredients.each(function () {
            $(this).on("change", shopList.checkItem)
        });
    },
    checkItem(e) {
        e.preventDefault();
        let selection = $(this);
        let url = selection.attr("formaction");
        let id = selection.attr("id");
        let $itemDetails = $("#" + id + " ~ " + shopList.itemDetails);
        if (!$itemDetails.hasClass("completed")) {
            $.ajax({
                url: url,
                dataType: "json",
                success: $itemDetails.addClass("completed")
            });
        }
        else {
            $.ajax({
                url: url,
                dataType: "json",
                success: $itemDetails.removeClass("completed")
            });
        }
    }
}



