$(window).on('load', function(){
    $('.user-progress').css('width','640px');
    var progress = document.querySelectorAll(".user-progress");

    let images = progress.length;
    let renderingState = 0;
    
    for (let index = 0; index < progress.length; index++) {
        const element = progress[index];
        html2canvas(element, {
            width: 640,
        }).then(canvas => {
            base64 = canvas.toDataURL();
            base64 = base64.substring(base64.indexOf(",") + 1);

            $('form').append(
                $('<input type="hidden" name="img[]">').val(base64)
            );
            $('.user-progress').css('width','auto');

            if(images - 1 <= ++renderingState){
                $('.convert-to-image-button').prop("disabled", false);
            }
        }).catch((res) => {});
    }
});
