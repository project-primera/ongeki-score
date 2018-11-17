$(window).on('load', function(){
    $('#user-progress').css('width','640px');
    html2canvas(document.querySelector("#user-progress"), {
        width: 640,
    }).then(canvas => {
        base64 = canvas.toDataURL();
        base64 = base64.substring(base64.indexOf(",") + 1);
        $('input[name="img"]').val(base64);
        $('.convert-to-image-button').prop("disabled", false);
        $('#user-progress').css('width','auto');
    }).catch((res) => {});
});
