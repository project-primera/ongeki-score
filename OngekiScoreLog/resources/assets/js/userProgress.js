$('.convert-to-image-button').on('click',function(){
    $('#user-progress').css('width','640px');
    html2canvas(document.querySelector("#user-progress"), {
        width: 640,
    }).then(canvas => {
        var w = window.open();
        w.document.write('<img src="' + canvas.toDataURL() + '" />');
        $('#user-progress').css('width','auto');
    });
});