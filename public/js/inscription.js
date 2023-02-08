
function toggleInscription(url, id) {
    $.ajax({
        method: "POST",
        url: url,
        data: {id: id},
        success: function (result) {
            if (result.info !== '') {

            }
            console.log(result.info);
            if (url.includes("inscription")) {
                $("#inscription_" + id).hide();
                $("#desistement_" + id).show();
            } else {
                $("#inscription_" + id).show();
                $("#desistement_" + id).hide();
            }
        }
    });
}
