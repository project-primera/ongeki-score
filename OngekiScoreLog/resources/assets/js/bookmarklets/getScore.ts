import * as $ from 'jquery';
import axios from 'axios';
import * as qs from 'qs';

(function () {
    const NET_DOMAIN = "ongeki-net.com";
    const NET_URL = "https://" + NET_DOMAIN + "/ongeki-mobile";
    const TOOL_URL = process.env.MIX_APP_URL;
    const API_URL = TOOL_URL + "/api/v2";

    const REQUEST_KEY = "?t=";
    const PRODUCT_NAME = process.env.MIX_APP_NAME;
    const VERSION = process.env.MIX_APPLICATION_VERSION;
    const COMMIT_HASH = process.env.MIX_COMMIT_HASH;

    const SLEEP_MSEC = 1000;

    class SameNameMusicList {
        static async get() {
            let result = await axios.get(API_URL + "/music/samename");
            return result.data;
        }

        // 曲名/アーティスト/ジャンルが同じ譜面の識別用アーティストを返す
        static async getArtistOfSameNameAndGenre(name: string, level: number) {
            // FIXME: 曲名ベタ打ち そのうち改善したいね
            if (name == "Perfect Shining!!") {
                if (level === 0) {
                    return 'ロケテスト譜面';
                } else {
                    return '通常譜面';
                }
            }

            return "";
        }
    }


    class PaymentStatus {
        private isStandardPlan: boolean = false;
        private isPremiumPlan: boolean = false;

        public IsPremiumPlan() {
            return this.isPremiumPlan;
        }

        /**
         * 課金状況を取得する
         */
        public async GetPaymentStatus() {
            await axios.get(NET_URL + '/courseDetail/', {
            }).then(async (response) => {
                await this.parsePaymentStatus(response.data);
            }).catch(function (error) {
                throw new Error("課金状況の取得に失敗しました。<br>" + error);
            });
        }

        private async parsePaymentStatus(html: string) {
            var parseHTML = $.parseHTML(html);
            this.isStandardPlan = ($(parseHTML).find(".back_course_standard").find("span").text() === "利用中");
            this.isPremiumPlan = ($(parseHTML).find(".back_course_premium").find("span").text() === "利用中");
        }
    }

    class PlayerData {
        trophy: string = "";
        level: number = -1;
        name: string = "";
        battle_point: number = 0;
        rating: number = 0;
        rating_max: number = 0;
        money: number = 0;
        total_money: number = 0;
        total_play: number = 0;
        comment: string = "";
        friend_code: number = 0;

        public async GetData() {
            await this.getPlayerDataFromNet();
            await sleep(SLEEP_MSEC);
            await this.getFriendCodeDataFromNet();
        }

        public async Validate() {
            if (this.level == -1) {
                throw Error("プレイヤー情報を取得できませんでした。オンゲキNETにログインしてもう一度実行してください。<br>ログインしている場合は時間を開けてお試しください。(一定期間にアクセスをしすぎると制限が掛かります)");
            }
        }

        private async getPlayerDataFromNet() {
            await axios.get(NET_URL + '/home/playerDataDetail/', {
            }).then(async (response) => {
                await this.parsePlayerData(response.data);
            }).catch(function (error) {
                throw new Error("プレイヤー情報の取得に失敗しました。<br>" + error);
            });
        }

        private async parsePlayerData(html: string) {
            var parseHTML = $.parseHTML(html);
            this.trophy = $(parseHTML).find(".trophy_block").find("span").text();
            this.level = +$(parseHTML).find(".lv_block").find("span").text();
            this.level += ((+($(parseHTML).find(".reincarnation_block").find("span").text())) * 100);
            this.name = $(parseHTML).find(".name_block").find("span").text();
            this.battle_point = +$(parseHTML).find(".battle_rank_block").find("div").text().replace(/,/g, "");
            this.rating = +$(parseHTML).find(".rating_new_block").find(".rating_field").find("[class^='rating_']").eq(0).text();
            this.rating_max = +$(parseHTML).find(".rating_new_block").find(".rating_field").find(".f_11").text().replace(/（MAX /g, "").replace(/）/g, "");
            this.money = +$(parseHTML).find(".user_data_detail_block").find("td").eq(2).text().split("（")[0].replace(/,/g, "");
            this.total_money = +$(parseHTML).find(".user_data_detail_block").find("td").eq(2).text().split("（")[1].replace(/累計 /g, "").replace(/）/g, "").replace(/,/g, "");
            this.total_play = +$(parseHTML).find(".user_data_detail_block").find("td").eq(5).text();
            this.comment = $(parseHTML).find(".comment_block").parent().text().replace(/	/g, "").replace("\n", "").replace("\n", "");
        }

        private async getFriendCodeDataFromNet() {
            await axios.get(NET_URL + '/friend/userFriendCode/', {
            }).then(async (response) => {
                await this.parseUserFriendCodeData(response.data);
            }).catch(function (error) {
                throw new Error("フレンドコードの取得に失敗しました。" + error);
            });
        }

        private async parseUserFriendCodeData(html: string) {
            var parseHTML = $.parseHTML(html);
            this.friend_code = +$(parseHTML).find(".friendcode_block").text();
        }
    }

    class SongInfo {
        title: string = "";
        difficulty: Difficulty = 0;
        genre: string = "";
        level: number = 0;
        over_damage_high_score: number = 0;
        battle_high_score: number = 0;
        technical_high_score: number = 0;
        full_bell: boolean = false;
        full_combo: boolean = false;
        all_break: boolean = false;
        artist: string = "";
        platinum_score: number = 0;
        star: number = 0;

        constructor(title: string, difficulty: Difficulty, genre: string,
            level: number, over_damage_high_score: number,
            battle_high_score: number, technical_high_score: number,
            full_bell: boolean, full_combo: boolean, all_break: boolean,
            artist: string, platinum_score: number, star: number
        ) {
            this.title = title;
            this.difficulty = difficulty;
            this.genre = genre;
            this.level = level;
            this.over_damage_high_score = over_damage_high_score;
            this.battle_high_score = battle_high_score;
            this.technical_high_score = technical_high_score;
            this.full_bell = full_bell;
            this.full_combo = full_combo;
            this.all_break = all_break;
            this.artist = artist;
            this.platinum_score = platinum_score;
            this.star = star;
        }
    }

    enum Difficulty {
        Basic = 0,
        Advanced = 1,
        Expert = 2,
        Master = 3,
        Lunatic = 10,
    }

    class ScoreData {
        private songInfos = new Array<SongInfo>();
        private sameNameList = null;
        // 空の場合は全曲取得のフラグとして扱う
        private songToUpload = [];

        public async GetArrayLength() {
            return this.songInfos.length;
        }

        public async GetUploadArrayLength() {
            return this.songToUpload.length;
        }

        public async GetPartialArray($page = 0) {
            return this.songInfos.slice($page * 50, $page * 50 + 50);
        }

        public async Clear() {
            this.songInfos = new Array<SongInfo>();
        }

        public async GetDifficultyScoreData(difficulty: Difficulty) {
            await this.getScoreHtmlFromNet(difficulty);
        }

        private async getScoreHtmlFromNet(difficulty: Difficulty) {
            await axios.get(NET_URL + '/record/musicGenre/search/', {
                params: {
                    genre: 99,
                    diff: difficulty
                }
            }).then(async (response) => {
                await this.parseScoreData(response.data, difficulty);
            }).catch(function (error) {
                throw new Error("難易度" + difficulty + "のスコア取得に失敗しました。" + error);
            });
        }

        public async getScoreHtmlFromRecent(timestamp: Date) {
            try {
                const response = await axios.get(NET_URL + '/record/playlog/');
                await this.checkNewScoreFromRecent(response.data, timestamp);
            } catch (error) {
                throw new Error("プレイ履歴からのスコア取得に失敗しました。" + error);
            }
        }

        private async checkNewScoreFromRecent(html: string, timestamp: Date) {
            if (this.sameNameList === null) {
                this.sameNameList = await SameNameMusicList.get();
            }
            var parseHTML = $.parseHTML(html);
            var $innerContainer3 = $(parseHTML).find(".container3").children(".m_10")
            // 50曲存在するなら50曲目をとって、更新日時が最終更新日時以降なら全曲取得に変更
            if ($innerContainer3.length === 50) {
                let recentUpdate = new Date($innerContainer3.last().find("span.f_r.h_10").text());
                if (recentUpdate > timestamp) {
                    this.songToUpload = [];
                    return;
                }
            }
            $innerContainer3.each((key, value) => {
                if ($(value).hasClass("m_10")){
                    if (new Date($(value).find("span.f_r.h_10").text()) > timestamp) {
                        var $elem = $(value).find(".m_5.l_h_10.break");
                        // 履歴から取得した曲名には改行コードとタブ制御文字が含まれているので削除
                        var songTitle = $elem.text().replace(/\n/g,"").replace(/\t/g,"");
                        if (this.songToUpload.indexOf(songTitle) === -1) {
                            this.songToUpload.push(songTitle);
                        }
                    }
                }
            });
        }

        private async parseScoreData(html: string, difficulty: Difficulty) {
            if (this.sameNameList === null) {
                this.sameNameList = await SameNameMusicList.get();
            }
            var parseHTML = $.parseHTML(html);
            var $innerContainer3 = $(parseHTML).find(".container3").find("div");

            var genre: string = "";
            for (var i = 0; i < $innerContainer3.length; ++i) {
                var value = $innerContainer3.eq(i);
                if ($(value).hasClass("p_5 f_20")) {
                    genre = $(value).text();
                } else if ($(value).hasClass("basic_btn")) {
                    for (var j = 0; j < $(value).length; ++j) {
                        await this.parseSingleMusic($(value).eq(j), value, difficulty, genre);
                    }
                }
            }
        }

        private async parseSingleMusic(element, parentElement, difficulty, genre) {
            let name = $(element).find(".music_label").text();
            let level = +($(element).find(".score_level").text().replace("+", ".5"));
            let artist = '';

            // プレイ履歴から取得した曲があるかチェック
            if (this.songToUpload.length !== 0) {
                // プレイ履歴に存在しない曲ならスキップする
                if (this.songToUpload.indexOf(name) === -1) {
                    return;
                }
            }

            if (this.sameNameList.indexOf(name) !== -1) {
                console.log("曲名が重複している楽曲名: " + name + ' / Lv' + level + ' / ' + genre + ' / ' + difficulty);
                await sleep(SLEEP_MSEC);

                artist = await SameNameMusicList.getArtistOfSameNameAndGenre(name, level);
                if (artist === "") {
                    let response = await axios.get(NET_URL + '/record/musicDetail/?idx=' + encodeURIComponent($(parentElement).find("[name=idx]").prop("value")));
                    let parse = $.parseHTML(response.data);
                    artist = $(parse).find("div.m_5.f_13.break").text().trim();
                    artist = artist.substring(0, artist.indexOf('\n'));
                }
                console.log(artist);
            }

            let platinumScore = +$($(element).find(".platinum_high_score_text_block")).text().replace(/,/g, "").split("/")[0];
            let star = +($($(element).find(".platinum_high_score_star_block").find(".f_b")).text());
            let isAb = ($(element).find("[src*='music_icon_ab.png']").length > 0
                || $(element).find("[src*='music_icon_abplus.png']").length > 0
            );
            let isFc = (isAb || $(element).find("[src*='music_icon_fc.png']").length > 0);

            let song = new SongInfo(
                name,
                difficulty,
                genre,
                level,
                +$($(element).find(".score_value")[0]).text().replace(/,/g, "").replace(/%/g, ""),
                +$($(element).find(".score_value")[1]).text().replace(/,/g, ""),
                +$($(element).find(".score_value")[2]).text().replace(/,/g, ""),
                $(element).find("[src*='music_icon_fb.png']").length > 0,
                isFc,
                isAb,
                artist,
                platinumScore,
                star,
            );
            this.songInfos.push(song);
        }
    }

    class TrophyInfo {
        name: string;
        detail: string;
        rank: string;

        constructor(name: string, detail: string, rank: string) {
            this.name = name;
            this.detail = detail;
            this.rank = rank;
        }
    }

    class TrophyData {
        trophyInfos: Array<TrophyInfo> = new Array<TrophyInfo>();

        public async GetArrayLength() {
            return this.trophyInfos.length;
        }

        public async GetPartialArray($page = 0) {
            return this.trophyInfos.slice($page * 50, $page * 50 + 50);
        }

        async getData() {
            await this.getAllRankTrophyDataFromNet();
        }

        private async getAllRankTrophyDataFromNet() {
            await axios.get(NET_URL + '/collection/trophy/', {
            }).then(async (response) => {
                await this.parseAllTrophyData(response.data);
            }).catch(function (error) {
                throw new Error("称号の取得に失敗しました。" + error);
            });
        }

        private async parseAllTrophyData(html: string) {
            var parseHTML = $.parseHTML(html);

            await ["Normal", "Silver", "Gold", "Platinum", "Rainbow"].forEach(async (value, index, array) => {
                var $listDiv = $(parseHTML).find("#" + value + "List");
                $listDiv.find(".m_10").each((key, v) => {
                    var trophy = new TrophyInfo(
                        $($(v).find(".f_14")).text(),
                        $($(v).find(".detailText")).text(),
                        value
                    );
                    this.trophyInfos.push(trophy);
                });
            });
        }
    }

    class CharacterFriendlyData {
        friendly: { [key: string]: number } = {};

        async getData() {
            await this.getCharacterFriendlyDataFromNet();
        }

        private async getCharacterFriendlyDataFromNet() {
            await axios.get(NET_URL + '/character/', {
            }).then(async (response) => {
                await this.parseCharacterFriendlyData(response.data);
            }).catch(function (error) {
                throw new Error("親密度情報の取得に失敗しました。" + error);
            });
        }

        private async parseCharacterFriendlyData(html: string) {
            var parseHTML = $.parseHTML(html);
            var $chara_btn = $(parseHTML).find(".chara_btn");
            await $chara_btn.each((key, value) => {
                var characterID: string = $(value).find("input").val() as string || "";

                var friendlyTensPlace: string = ($(value).find(".character_friendly_conainer").find("img").eq(1).attr('src') || "0").replace("https://ongeki-net.com/ongeki-mobile/img/friendly/num_", "").replace("0.png", "");
                var friendlyUnitsPlace: string = ($(value).find(".character_friendly_conainer").find("img").eq(2).attr('src') || "0").replace("https://ongeki-net.com/ongeki-mobile/img/friendly/num_", "").replace(".png", "");

                this.friendly[characterID] = +(friendlyTensPlace + friendlyUnitsPlace);
            });
        }
    }

    /**
     * プラチナスコア枠
     */
    class PlatinumMusicInfo {
        title: string = "";
        difficulty: number = 0;
        platinumScore: number = 0;
        star: number = 0;
        genre: string = "";
        artist: string = "";
        constructor(title: string, difficulty: number, platinumScore: number,
            star: number, genre: string = "", artist: string = "") {
            this.title = title;
            this.difficulty = difficulty;
            this.platinumScore = platinumScore;
            this.star = star;
            this.genre = genre;
            this.artist = artist;
        }
    }
    class RatingPlatinumMusicData {
        ratingPlatinumMusicObject: Array<PlatinumMusicInfo> = new Array<PlatinumMusicInfo>();

        async getData() {
            await this.getRatingPlatinumMusicDataFromNet();
        }

        private async getRatingPlatinumMusicDataFromNet() {
            await axios.get(NET_URL + '/home/ratingTargetMusic/', {
            }).then(async (response) => {
                await this.parseRatingPlatinumMusicData(response.data);
            }).catch(function (error) {
                throw new Error("レーティング対象曲の取得に失敗しました。" + error);
            });
        }

        private async parseRatingPlatinumMusicData(html: string) {
            let sameNameList = await SameNameMusicList.get();
            let parseHTML = $.parseHTML(html);
            let $basic_btn = $(parseHTML).find(".basic_btn");

            const MAX_PLATINUM_COUNT = 50;
            let count = 0;
            for await (const value of $basic_btn) {
                if ($(value).html().match(/PLATINUM HIGH SCORE/)) {
                    // 候補枠が取れてしまうので50曲取ったら抜ける
                    if (++count > MAX_PLATINUM_COUNT) {
                        break;
                    }
                    let difficulty: number = -1;
                    if ($(value).hasClass('lunatic_score_back')) {
                        difficulty = 10;
                    } else if ($(value).hasClass('master_score_back')) {
                        difficulty = 3;
                    } else if ($(value).hasClass('expert_score_back')) {
                        difficulty = 2;
                    } else if ($(value).hasClass('advanced_score_back')) {
                        difficulty = 1;
                    } else if ($(value).hasClass('basic_score_back')) {
                        difficulty = 0;
                    }

                    let name = $(value).find(".music_label").text();
                    let genre = "";
                    let artist = "";
                    let platinumScore = +$($(value).find(".platinum_score_text_block")).text().replace(/,/g, "").split("/")[0];
                    let star = +($($(value).find(".platinum_high_score_star_block").find(".f_b")).text());

                    if (sameNameList.indexOf(name) !== -1) {
                        let level = +($(value).find(".score_level").text().replace("+", ".5"));
                        console.log("曲名が重複している楽曲名: " + name + ' / Lv' + level + ' / ' + genre + ' / ' + difficulty);
                        await sleep(SLEEP_MSEC);

                        artist = await SameNameMusicList.getArtistOfSameNameAndGenre(name, level);
                        if (artist === "") {
                            let result = await axios.get(NET_URL + '/record/musicDetail/?idx=' + encodeURIComponent($(value).find("[name=idx]").prop("value")));
                            let parse = $.parseHTML(result.data);
                            genre = $(parse).find("div.t_r.f_12.main_color").text().trim();
                            artist = $(parse).find("div.m_5.f_13.break").text().trim();
                            artist = artist.substring(0, artist.indexOf('\n'));
                        }
                        console.log(genre + ' / ' + artist);
                    }

                    let info: PlatinumMusicInfo = new PlatinumMusicInfo(
                        name,
                        difficulty,
                        platinumScore,
                        star,
                        genre,
                        artist
                    );
                    this.ratingPlatinumMusicObject.push(info);
                }
            }
        }
    }

    enum MethodType {
        Player = 0,
        Score = 1,
        Trophy = 2,
        CharacterFriendly = 3,
        RatingRecentMusic = 4,
        Payment = 5,
        RatingPlatinumMusic = 6,
    }

    class PostData {
        token: string;
        hash: string;

        constructor(token: string, hash: string) {
            this.token = token;
            this.hash = hash;
        }

        public async Post(methodType: MethodType, data: PaymentStatus
            | PlayerData | Array<SongInfo> | Array<TrophyInfo>
            | CharacterFriendlyData | RatingPlatinumMusicData)
        {
            let d = {
                'hash': this.hash,
                'methodType': methodType,
                'data': data,
            };
            await axios.post(API_URL + "/user/update", qs.stringify(d), {
                headers: {
                    Authorization: "Bearer " + this.token,
                }
            }).then(async result => {
                if (result.data.isError === void 0 || result.data.isError) {
                    throw new Error("データの登録に失敗しました。<br>" + result.data.message.join("<br>"));
                }
                echo("完了", false);

                if (result.data.message.length !== 0) {
                    echo(result.data.message.join('<br>'));
                }
            }).catch(async function (error) {
                throw new Error("不明なエラーが発生しました。<br>" + error);
            });
        }
    }

    var sleep = (function (milliseconds: number) {
        return new Promise<void>(resolve => {
            setTimeout(() => resolve(), milliseconds);
        });
    });

    var getToken = (function () {
        let url: string;
        if (document.currentScript) {
            url = (document.currentScript as HTMLScriptElement).src;
        } else {
            var scripts = document.getElementsByTagName('script'),
                script = scripts[scripts.length - 1];
            if (script.src) {
                url = script.src;
            } else {
                url = "";
            }
        }
        return url.slice(url.indexOf(REQUEST_KEY) + REQUEST_KEY.length);
    });

    let $textarea = null;
    let echo = async (message: string, isNewLine: boolean = true) => {
        if (isNewLine) {
            $textarea.append("<br>" + message);
        } else {
            $textarea.append(message);
        }
        $textarea.scrollTop(Number.MAX_SAFE_INTEGER);
    }

    let getTime = async () => {
        let d = new Date();
        return (("0" + d.getHours().toString()).slice(-2) + ":" + ("0" + d.getMinutes().toString()).slice(-2) + ":" + ("0" + d.getSeconds().toString()).slice(-2) + " ");
    }

    /**
     * main
     */
    let main = async () => {
        $("body").scrollTop(0).attr("style", "overflow-y: hidden;");
        let $overlay = $("<div>").addClass("ongeki_score").attr("style", "color:#222; font-size: 1em; padding-top: 120px; width: 100%; height:100%; position: fixed; top: 0; z-index: 1000; background: rgba(0,0,0,0.3);");
        $("body").append($overlay);
        $textarea = $("<div>").attr("style", "background-color: #eee; width:480px; height:100%; margin:0 auto; padding: 0.5em 1em;  overflow-y: scroll;")
        $overlay.append($textarea);

        echo(PRODUCT_NAME, false)
        if (VERSION != void 0) {
            echo(" ver." + VERSION, false);
        }
        if (COMMIT_HASH != void 0) {
            echo("/" + COMMIT_HASH, false);
        }

        let token: string = getToken();
        let userId: number = 0;
        let hash: string = "";
        let name: string = "";
        let lastUpdate: Date = new Date();

        try {
            // メンテナンスチェック
            await axios.get(API_URL + "/user/update/status", {
                headers: {
                    Authorization: "Bearer " + token,
                }
            }).then(result => {
                if (result.data.message === void 0 || result.data.id === void 0 || result.data.name === void 0 || result.data.hash === void 0) {
                    throw new Error("不明なエラーが発生しました。");
                } else if (result.data.message != "ok") {
                    throw new Error(result.data.message);
                }
                userId = result.data.id;
                hash = result.data.hash;
                name = result.data.name;
                lastUpdate = new Date(result.data.updateAt);

            }).catch(function (error) {
                    throw new Error(error + "<br>スコアツールサーバーへの接続に失敗しました。<br><br>まずは以下の手順をお試しください。<br>1)ブックマークレットの再生成を行い、現在ご利用のブックマークレットに上書きして再度実行してください。<br>２）メンテナンス情報をご確認ください。情報については<a href='https://twitter.com/ongeki_score' target='_blank' style='color:#222'>Twitter@ongeki_score</a>にてお知らせします。");
                });

            echo("ユーザー確認: " + name + "さん(id: " + userId + ")<hr>");

            // 実行場所チェック
            if (NET_DOMAIN != window.location.hostname) {
                throw new Error("オンゲキNETで実行してください。");
            }

            let postData = new PostData(token, hash);

            echo(await getTime() + "課金状況を取得します。");
            let paymentStatus = new PaymentStatus;
            await paymentStatus.GetPaymentStatus();
            echo(await getTime() + "課金状況を送信します...");
            await postData.Post(MethodType.Payment, paymentStatus);
            await sleep(SLEEP_MSEC);

            echo(await getTime() + "プレイヤーデータを取得します。");
            let playerData = new PlayerData;
            await playerData.GetData();
            await playerData.Validate();
            echo(await getTime() + "プレイヤーデータを送信します...");
            await postData.Post(MethodType.Player, playerData);
            await sleep(SLEEP_MSEC);

            let scoreData = new ScoreData;
            let length = 0;

            if (paymentStatus.IsPremiumPlan()) {
                try {
                    echo(await getTime() + "プラチナスコア枠対象曲を取得します。");
                    let ratingPlatinumMusicData = new RatingPlatinumMusicData;
                    await ratingPlatinumMusicData.getData();
                    echo(await getTime() + "プラチナスコア枠対象曲を送信します...");
                    console.log(ratingPlatinumMusicData);
                    await postData.Post(MethodType.RatingPlatinumMusic, ratingPlatinumMusicData);
                    await sleep(SLEEP_MSEC);
                } catch (error) {
                    echo(await getTime() + "プラチナスコア枠対象曲の取得に失敗しました。<br>対象曲が1曲以上あるかを確認してください。");
                }
            } else {
                echo(await getTime() + "スタンダードプランの為、レーティング対象曲情報取得をスキップします。");
            }

            echo(await getTime() + "更新対象曲を取得します。");
            await scoreData.getScoreHtmlFromRecent(lastUpdate);
            if (await scoreData.GetUploadArrayLength() === 0) {
                echo(await getTime() + "前回更新からのプレイ曲数が50曲を超えているか、プレイが行われていないため全件取得を行います。");
            } else {
                echo(await getTime() + "前回更新からの曲のみを取得します。<br>全件取得を行いたい場合はもう一度実行してください。");
                console.log("前回更新からのプレイ曲数: " + await scoreData.GetUploadArrayLength() + "曲");
            }
            await sleep(SLEEP_MSEC);

            echo(await getTime() + "Lunaticのスコアデータを取得します。");
            scoreData.Clear();
            await scoreData.GetDifficultyScoreData(Difficulty.Lunatic);
            echo(await getTime() + "スコアデータを送信します。");
            length = Math.ceil(await scoreData.GetArrayLength() / 50);
            for (let index = 0; index < length; ++index) {
                echo(await getTime() + " " + (index + 1) + ": 送信...");
                await postData.Post(MethodType.Score, await scoreData.GetPartialArray(index));
                await sleep(SLEEP_MSEC / 10);
            }
            await sleep(SLEEP_MSEC);

            echo(await getTime() + "Masterのスコアデータを取得します。");
            scoreData.Clear();
            await scoreData.GetDifficultyScoreData(Difficulty.Master);
            echo(await getTime() + "スコアデータを送信します。");
            length = Math.ceil(await scoreData.GetArrayLength() / 50);
            for (let index = 0; index < length; ++index) {
                echo(await getTime() + " " + (index + 1) + ": 送信...");
                await postData.Post(MethodType.Score, await scoreData.GetPartialArray(index));
                await sleep(SLEEP_MSEC / 10);
            }
            await sleep(SLEEP_MSEC);

            echo(await getTime() + "Expertのスコアデータを取得します。");
            scoreData.Clear();
            await scoreData.GetDifficultyScoreData(Difficulty.Expert);
            echo(await getTime() + "スコアデータを送信します。");
            length = Math.ceil(await scoreData.GetArrayLength() / 50);
            for (let index = 0; index < length; ++index) {
                echo(await getTime() + " " + (index + 1) + ": 送信...");
                await postData.Post(MethodType.Score, await scoreData.GetPartialArray(index));
                await sleep(SLEEP_MSEC / 10);
            }
            await sleep(SLEEP_MSEC);

            echo(await getTime() + "Advancedのスコアデータを取得します。");
            scoreData.Clear();
            await scoreData.GetDifficultyScoreData(Difficulty.Advanced);
            echo(await getTime() + "スコアデータを送信します。");
            length = Math.ceil(await scoreData.GetArrayLength() / 50);
            for (let index = 0; index < length; ++index) {
                echo(await getTime() + " " + (index + 1) + ": 送信...");
                await postData.Post(MethodType.Score, await scoreData.GetPartialArray(index));
                await sleep(SLEEP_MSEC / 10);
            }
            await sleep(SLEEP_MSEC);

            echo(await getTime() + "Basicのスコアデータを取得します。");
            scoreData.Clear();
            await scoreData.GetDifficultyScoreData(Difficulty.Basic);
            echo(await getTime() + "スコアデータを送信します。");
            length = Math.ceil(await scoreData.GetArrayLength() / 50);
            for (let index = 0; index < length; ++index) {
                echo(await getTime() + " " + (index + 1) + ": 送信...");
                await postData.Post(MethodType.Score, await scoreData.GetPartialArray(index));
                await sleep(SLEEP_MSEC / 10);
            }
            await sleep(SLEEP_MSEC);

            echo(await getTime() + "称号獲得状況を取得します。");
            let trophyData = new TrophyData;
            await trophyData.getData();
            echo(await getTime() + "称号獲得状況を送信します。");
            length = Math.ceil(await trophyData.GetArrayLength() / 50);
            for (let index = 0; index < length; ++index) {
                echo(await getTime() + " " + index + ": 送信...");
                await postData.Post(MethodType.Trophy, await trophyData.GetPartialArray(index));
                await sleep(SLEEP_MSEC / 10);
            }
            await sleep(SLEEP_MSEC);

            // echo(await getTime() + "キャラクターの親密度情報を取得します。");
            // let characterFriendlyData = new CharacterFriendlyData;
            // await characterFriendlyData.getData();
            // echo(await getTime() + "キャラクターの親密度情報を送信します...");
            // await postData.Post(MethodType.CharacterFriendly, characterFriendlyData);
            // await sleep(SLEEP_MSEC);




            echo("データの登録に成功しました！");

        } catch (error) {
            echo("エラーが発生しました。<br>" + error.message + "<br>");
            let now = new Date();
            let today = new Date();
            echo("再度お試しいただいても解決しない場合は、お手数をおかけしますが以下のリンクまで以下のデータを添えてご報告をお願い致します。");
            echo(" <a href='https://twitter.com/ongeki_score' style='color:#222'>Twitter</a> / <a href='https://github.com/Slime-hatena/ProjectPrimera/issues' style='color:#222'>Github issue</a>");
            echo(today.getFullYear() + "/" + (today.getMonth() + 1) + "/" + today.getDate() + " " + now.toLocaleTimeString());
        }
        echo("<br><a href='" + TOOL_URL + "/user/" + userId + "/progress' style='color:#222'>更新差分はこちら</a>");
        echo("<br><a href='" + NET_URL + "/home' style='color:#222'>オンゲキNETに戻る</a> / <a href='" + TOOL_URL + "' style='color:#222'>OngekiScoreLogに戻る</a>")
    }
    main();
})();
