var progress = document.querySelectorAll(".user-progress");
let images = progress.length;
let renderingState = 0;

async function convert(element: HTMLElement, index: number) {
    let pr: Html2CanvasPromise<HTMLCanvasElement> = html2canvas(element, {
        width: 640,
    });

    await pr.then(canvas => {
        ++renderingState
        let base64 = canvas.toDataURL();

        $('div#generate_images').append(
            $("<img>").attr("src", base64)
        );

        $('.progress').val(renderingState / images * 100);
        $(".progress-message").text("画像化中: " + renderingState + "/" + images + "(" + Math.round(renderingState / images * 100) + "%)");

    }).catch((res) => {
        $(".progress-message").text("エラーが発生しました。<br>" + JSON.stringify(res));
        throw res;
    });
}

$(function ($) {
    $('#select-generation').change(function () {
        $('.select-generations-option').prop("disabled", true);
        $('#select-generation').addClass("is-loading");
        location.href = $('#current-url').text() + "/" + $('#select-generation  option:selected').val();
    });

    $('#submit_button').click(async function () {
        $('#submit_button').prop("disabled", true);
        $('.user-progress').css('width', '640px');
        $('html').css('overflow', 'hidden');
        $('body').css('overflow', 'hidden');
        window.scrollTo(0, 0);
        document.addEventListener('touchmove', function (e) { e.preventDefault(); }, { passive: false });

        $(".progress-message").text("画像化中: " + renderingState + "/" + images + "(0%)");

        for (let index = 0; index < progress.length; index++) {
            const element: HTMLElement = <HTMLScriptElement>progress[index];

            await convert(element, index);
        }

        $('.progress').removeClass("is-progress").removeAttr("value").removeAttr("max");
    })
});
