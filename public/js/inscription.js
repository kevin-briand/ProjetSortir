
function toggleInscription(url, id) {
    $.ajax({
        method: "POST",
        url: url,
        data: {id: id},
        success: function (result) {
            if(setMessageBox(result)) {
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
        }
    });
}

function toggleAnnulation(url, id) {
    $.ajax({
        method: "POST",
        url: url,
        data: {id: id},
        success: function (result) {
            if (setMessageBox(result)) {
                $("#annulation_" + id).hide();
            } else {
                $("#annulation_" + id).show();
            }
        }
    });
}

function togglePublication(url, id) {
    $.ajax({
        method: "POST",
        url: url,
        data: {id: id},
        success: function (result) {
            if (setMessageBox(result)) {
                $("#publication_" + id).hide();
            } else {
                $("#publication_" + id).show();
            }
        }
    });
}

function setMessageBox(json) {
    const messageBox = $("#messages");
    let isOk = false;
    if (json.info) {
        messageBox.addClass("alert alert-success");
        messageBox.removeClass("alert-danger");
        messageBox.text(json.info);
        isOk = true;
    } else {
        messageBox.addClass("alert alert-danger");
        messageBox.removeClass("alert-success");
        messageBox.text(json.error);
    }
    messageBox.show();
    return isOk;
}