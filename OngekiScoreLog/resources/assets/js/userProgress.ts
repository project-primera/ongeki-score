var progress = document.querySelectorAll(".user-progress");
let images = progress.length;
let renderingState = 0;

async function convert(element: HTMLElement, index: number){
    let pr: Html2CanvasPromise<HTMLCanvasElement> = html2canvas(element, {
        width: 640,
    });

    await pr.then(canvas => {
        ++renderingState
        let base64 = canvas.toDataURL();
        base64 = base64.substring(base64.indexOf(",") + 1);

        $('form').append(
            $('<input type="hidden" name="img[' + index + ']">').val(base64)
        );

        $('.progress').val(renderingState / images * 100);
        $(".progress-message").text("画像化中: " + renderingState + "/" + images + "(" + Math.round(renderingState / images * 100) + "%)");

        if(images - 1 <= renderingState){
            // $('.user-progress').css('width','auto');
            $('.progress').val(100);
            $(".progress-message").text("ツイート中・・・");
            $('.convert-to-image-button').prop("disabled", false);
        }
    }).catch((res) => {
        $(".progress-message").text("エラーが発生しました。<br>" + JSON.stringify(res));
        throw res;
    });
}

$(function($) {
    $('#select-generation').change(function() {
        $('.select-generations-option').prop("disabled", true);
        $('#select-generation').addClass("is-loading");
        location.href = $('#current-url').text() + "/" + $('#select-generation  option:selected').val();
    });
    
    $('#submit_button').click(async function(){
        $('#submit_button').prop("disabled", true);
        $('.user-progress').css('width','640px');

        $(".progress-message").text("画像化中: " + renderingState + "/" + images + "(0%)");
        
        for (let index = 0; index < progress.length; index++) {
            const element: HTMLElement = <HTMLScriptElement>progress[index];

            await convert(element, index);
        }

        $('.progress').removeClass("is-progress").removeAttr("value").removeAttr("max");
        $('#tweet_form').submit();
    })
});
