@extends('layouts.app')

@section('title', "ツイート結果")
@section('hero_title', "ツイート結果")

@section('content')
    <article class="box">
        {{$result}}

        @if (isset($tweetID))
            <script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script>
            <div id="tweet-container"></div>
            <script type="text/javascript">
                var container = document.getElementById("tweet-container");
                twttr.widgets.createTweet(
                    "{{$tweetID}}", container
                ) ;
            </script>
        @endif
    </article>
@endsection