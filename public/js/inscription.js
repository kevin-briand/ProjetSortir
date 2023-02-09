
function toggleInscription(url, id) {
    $.ajax({
        method: "POST",
        url: url,
        data: {id: id},
        success: function (result) {
            const messageBox = $("#messages");
            if (result.info) {
                messageBox.addClass("alert alert-success");
                messageBox.removeClass("alert-danger");
                messageBox.text(result.info);

            } else {
                messageBox.addClass("alert alert-danger");
                messageBox.removeClass("alert-success");
                messageBox.text(result.error);
            }
            messageBox.show();
            if (url.includes("inscription")) {
                $("#inscription_" + id).hide();
                $("#desistement_" + id).show();
                $("#inscrit_" + id).text('X');
            } else {
                $("#inscription_" + id).show();
                $("#desistement_" + id).hide();
                $("#inscrit_" + id).text('');
            }
        }
    });
}
