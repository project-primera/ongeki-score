// import axios from  'axios'だと通らない・・・ なんで・・・
import axios from '../node_modules/axios/index';

(function () {
  console.log("run");

  const NET_URL = "https://ongeki-net.com/ongeki-mobile/";
  const TOOL_URL = "http://127.0.0.1:8000/api/user/update";

  const PRODUCT_NAME = "Project Primera - getScore";
  const VERSION = 1.0;

  class PlayerData {
    trophy: string = "";
    level: number = 0;
    name: string = "";
    battle_point: number = 0;
    rating: number = 0;
    rating_max: number = 0;
    money: number = 0;
    total_money: number = 0;
    total_play: number = 0;
    comment: string = "";
    friend_code: number = 0;

    async getData() {
      await this.getPlayerDataFromNet();
      await this.getFriendCodeDataFromNet();
    }

    private async getPlayerDataFromNet() {
      await axios.get(NET_URL + 'home/playerDataDetail/', {
      }).then(async (response) => {
        await this.parsePlayerData(response.data);
      }).catch(function (error) {
        //TODO: エラー処理書く
      });
    }

    private async parsePlayerData(html: string) {
      var parseHTML = $.parseHTML(html);
      this.trophy = $(parseHTML).find(".trophy_block").find("span").text();
      this.level = +$(parseHTML).find(".lv_block").find("span").text();
      this.name = $(parseHTML).find(".name_block").find("span").text();
      this.battle_point = +$(parseHTML).find(".battle_rank_block").find("div").text().replace(/,/g, "");
      this.rating = +$(parseHTML).find(".rating_block").find(".rating_field").find("[class^='rating_']").eq(0).text();
      this.rating_max = +$(parseHTML).find(".rating_block").find(".rating_field").find(".f_11").text().replace(/（MAX /g, "").replace(/）/g, "");
      this.money = +$(parseHTML).find(".user_data_detail_block").find("td").eq(2).text().split("（")[0].replace(/,/g, "");
      this.total_money = +$(parseHTML).find(".user_data_detail_block").find("td").eq(2).text().split("（")[1].replace(/累計 /g, "").replace(/）/g, "").replace(/,/g, "");
      this.total_play = +$(parseHTML).find(".user_data_detail_block").find("td").eq(5).text();
      this.comment = $(parseHTML).find(".comment_block").parent().text().replace(/	/g, "").replace("\n", "").replace("\n", "");
    }

    private async getFriendCodeDataFromNet() {
      await axios.get(NET_URL + 'friend/userFriendCode/', {
      }).then(async (response) => {
        await this.parseUserFriendCodeData(response.data);
      }).catch(function (error) {
        //TODO: エラー処理書く
      });
    }

    private async parseUserFriendCodeData(html: string) {
      var parseHTML = $.parseHTML(html);
      this.friend_code = +$(parseHTML).find(".friendcode_block").text();
    }
  }

  class SongInfo {
    title: string;
    over_damage_high_score: number;
    battle_high_score: number;
    technical_high_score: number;

    constructor(title: string, over_damage_high_score: number, battle_high_score: number, technical_high_score: number) {
      this.title = title;
      this.over_damage_high_score = over_damage_high_score;
      this.battle_high_score = battle_high_score;
      this.technical_high_score = technical_high_score;
    }
  }

  class ScoreData {
    basicSongInfos = new Array<SongInfo>();
    advancedSongInfos = new Array<SongInfo>();
    expertInfos = new Array<SongInfo>();
    masterSongInfos = new Array<SongInfo>();
    lunaticSongInfos = new Array<SongInfo>();

    async getData() {
      await this.getAllDifficultyScoreDataFromNet();
    }

    private async getAllDifficultyScoreDataFromNet() {
      await [0, 1, 2, 3, 10].forEach(async (value, index, array) => {
        await this.getScoreHtmlFromNet(value);
      });
    }

    private async getScoreHtmlFromNet(difficulty: number) {
      await axios.get(NET_URL + 'record/musicGenre/search/', {
        params: {
          genre: 99,
          diff: difficulty
        }
      }).then(async (response) => {
        await this.parseScoreData(response.data, difficulty);
      }).catch(function (error) {
        //TODO: エラー処理書く
      });
    }

    private async parseScoreData(html: string, difficulty: number) {
      var parseHTML = $.parseHTML(html);
      var $innerContainer3 = $(parseHTML).find(".basic_btn");

      await $innerContainer3.each((key, value) => {
        $(value).each((k, v) => {
          var song = new SongInfo(
            $(v).find(".music_label").text(),
            +$($(v).find(".score_value")[0]).text().replace(/,/g, "").replace(/%/g, ""),
            +$($(v).find(".score_value")[1]).text().replace(/,/g, ""),
            +$($(v).find(".score_value")[2]).text().replace(/,/g, "")
          );
          switch (difficulty) {
            case 0: this.basicSongInfos.push(song); break;
            case 1: this.advancedSongInfos.push(song); break;
            case 2: this.expertInfos.push(song); break;
            case 3: this.masterSongInfos.push(song); break;
            case 10: this.lunaticSongInfos.push(song); break;
          }
        });
      });
    }
  }

  class TrophyInfo {
    name: string;
    detail: string;

    constructor(name: string, detail: string) {
      this.name = name;
      this.detail = detail;
    }
  }

  class TrophyData {
    normalTrophyInfos: Array<TrophyInfo> = new Array<TrophyInfo>();
    silverTrophyInfos: Array<TrophyInfo> = new Array<TrophyInfo>();
    goldTrophyInfos: Array<TrophyInfo> = new Array<TrophyInfo>();
    platinumTrophyInfo: Array<TrophyInfo> = new Array<TrophyInfo>();

    async getData() {
      await this.getAllRankTrophyDataFromNet();
    }

    private async getAllRankTrophyDataFromNet() {
      axios.get(NET_URL + 'collection/trophy/', {
      }).then(async (response) => {
        await this.parseAllTrophyData(response.data);
      }).catch(function (error) {
        //TODO: エラー処理書く
      });
    }

    private async parseAllTrophyData(html: string) {
      var parseHTML = $.parseHTML(html);

      await ["Normal", "Silver", "Gold", "Platinum"].forEach(async (value, index, array) => {
        var $listDiv = $(parseHTML).find("#" + value + "List");
        $listDiv.find(".m_10").each((key, v) => {
          var trophy = new TrophyInfo(
            $($(v).find(".f_14")).text(),
            $($(v).find(".detailText")).text()
          );
          switch (value) {
            case "Normal": this.normalTrophyInfos.push(trophy); break;
            case "Silver": this.silverTrophyInfos.push(trophy); break;
            case "Gold": this.goldTrophyInfos.push(trophy); break;
            case "Platinum": this.platinumTrophyInfo.push(trophy); break;
          }
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
      await axios.get(NET_URL + 'character/', {
      }).then(async (response) => {
        await this.parseCharacterFriendlyData(response.data);
      }).catch(function (error) {
        //TODO: エラー処理書く
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

  class RecentMusicInfo {
    title: string = "";
    technicalScore: number = 0;

    constructor(title: string, technicalScore: number) {
      this.title = title;
      this.technicalScore = technicalScore;
    }
  }

  class RatingRecentMusicData {
    ratingRecentMusicObject: Array<RecentMusicInfo> = new Array<RecentMusicInfo>();

    async getData() {
      await this.getRatingRecentMusicDataFromNet();
    }

    private async getRatingRecentMusicDataFromNet() {
      await axios.get(NET_URL + 'home/ratingTargetMusic/', {
      }).then(async (response) => {
        await this.parseRatingRecentMusicData(response.data);
      }).catch(function (error) {
        //TODO: エラー処理書く
      });
    }

    private async parseRatingRecentMusicData(html: string) {
      var parseHTML = $.parseHTML(html);
      var $basic_btn = $(parseHTML).find(".basic_btn");
      var count: number = 0;

      await $basic_btn.each((key, value) => {
        if ($(value).html().match(/TECHNICAL SCORE/)) {
          var info: RecentMusicInfo = new RecentMusicInfo(
            $(value).find(".music_label").text(),
            +$(value).find(".score_value").text().replace(/,/g, "")
          );
          this.ratingRecentMusicObject.push(info);
        }
      });
    }
  }
  class AllData {
    playerData: PlayerData = new PlayerData();
    scoreData: ScoreData = new ScoreData();
    trophyData: TrophyData = new TrophyData();
    characterFriendlyData: CharacterFriendlyData = new CharacterFriendlyData();
    ratingRecentMusicData: RatingRecentMusicData = new RatingRecentMusicData();
  }


  let main = async () => {
    let allData: AllData = new AllData();

    await allData.playerData.getData();
    await allData.scoreData.getData();
    await allData.trophyData.getData();
    await allData.characterFriendlyData.getData();
    await allData.ratingRecentMusicData.getData();

    var json = JSON.stringify(allData);

    console.log(json);
    axios.post(TOOL_URL, json);
  }

  main();

})();
