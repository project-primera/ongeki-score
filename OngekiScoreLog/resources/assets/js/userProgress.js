$(function($) {
    $('#select-generation').change(function() {
        $('.select-generations-option').prop("disabled", true);
        $('#select-generation').addClass("is-loading");
        location.href = $('#current-url').text() + "/" + $('#select-generation  option:selected').val();
    });
    
    $('#progress').click(function(){
        $('#progress').prop("disabled", true);
    
        $('.user-progress').css('width','640px');
        var progress = document.querySelectorAll(".user-progress");
    
        let images = progress.length;
        let renderingState = 0;
    
        let status = [];
        
        for (let index = 0; index < progress.length; index++) {
            const element = progress[index];
            status[index] = "○";
            $('#image_status').html("画像化中: " + status.join("<wbr>"));
    
            html2canvas(element, {
                width: 640,
            }).then(canvas => {
                status[index] = "●";
                $('#image_status').html("画像化中: " + status.join("<wbr>"));
    
                base64 = canvas.toDataURL();
                base64 = base64.substring(base64.indexOf(",") + 1);
    
                $('form').append(
                    $('<input type="hidden" name="img[' + index + ']">').val(base64)
                );
                $('.user-progress').css('width','auto');
    
                if(images - 1 <= ++renderingState){
                $('#image_status').text("画像化中: 完了");
                    $('.convert-to-image-button').prop("disabled", false);
                }
            }).catch((res) => {});
        }
    })
});
