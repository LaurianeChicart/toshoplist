const hideActiveJsMessage = {
    messageId: "",
    constructor(messageId) {
        this.messageId = messageId;
        $("#" + this.messageId).hide();
    }
}
