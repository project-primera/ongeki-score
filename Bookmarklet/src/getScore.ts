// import axios from  'axios'だと通らない・・・ なんで・・・
import axios from '../node_modules/axios/index';

(function () {
  const NET_URL = "https://ongeki-net.com/ongeki-mobile/";
  const TOOL_URL = "https://example.net/";

  const PRODUCT_NAME = "Project Primera - getScore";
  const VERSION = 1.0;

  console.log("run");

  class PlayerData{
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

    constructor(){
      this.getPlayerDataFromNet();
      this.getFriendCodeDataFromNet();
    }

    private getPlayerDataFromNet(){
      axios.get(NET_URL + 'home/playerDataDetail/', {
      }).then((response) => {
        this.parsePlayerData(response.data);
      }).catch(function (error) {
        //TODO: エラー処理書く
      });
    }

    private parsePlayerData(html: string) {
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

    private getFriendCodeDataFromNet(){
      axios.get(NET_URL + 'friend/userFriendCode/', {
      }).then((response) => {
        this.parseUserFriendCodeData(response.data);
      }).catch(function (error) {
        //TODO: エラー処理書く
      });
    }

    private parseUserFriendCodeData(html: string) {
      var parseHTML = $.parseHTML(html);
      this.friend_code = +$(parseHTML).find(".friendcode_block").text();
    }
  }

  class SongInfo{
    title: string;
    over_damage_high_score: number;
    battle_high_score: number;
    technical_high_score: number;
    
    constructor(title: string, over_damage_high_score: number, battle_high_score: number, technical_high_score: number){
      this.title = title;
      this.over_damage_high_score = over_damage_high_score;
      this.battle_high_score = battle_high_score;
      this.technical_high_score = technical_high_score;
    }
  }

  class ScoreData{
    basicSongInfos = new Array<SongInfo>();
    advancedSongInfos = new Array<SongInfo>();
    expertInfos = new Array<SongInfo>();
    masterSongInfos = new Array<SongInfo>();
    lunaticSongInfos = new Array<SongInfo>();

    constructor(){
      this.getAllDifficultyScoreDataFromNet();
    }
    private getAllDifficultyScoreDataFromNet(){
      [0, 1, 2, 3, 10].forEach((value, index, array) => {
        this.getScoreHtmlFromNet(value);
      });
    }

    private getScoreHtmlFromNet(difficulty: number){
      axios.get(NET_URL + 'record/musicGenre/search/', {
        params: {
          genre: 99,
          diff: difficulty
        }
      }).then((response) => {
        this.parseScoreData(response.data, difficulty);
      }).catch(function (error) {
        //TODO: エラー処理書く
      });
    }

    private parseScoreData(html: string, difficulty: number) {
      var parseHTML = $.parseHTML(html);
      var $innerContainer3 = $(parseHTML).find(".basic_btn");
  
      $innerContainer3.each((key, value) => {
        $(value).each((k, v) => {
          var song = new SongInfo(
            $(v).find(".music_label").text(),
            +$($(v).find(".score_value")[0]).text(),
            +$($(v).find(".score_value")[1]).text(),
            +$($(v).find(".score_value")[2]).text()
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

  class TrophyInfo{
    name: string;
    detail: string;

    constructor(name: string, detail: string){
      this.name = name;
      this.detail = detail;
    }
  }

  class TrophyData{
    normalTrophyInfos: Array<TrophyInfo> = new Array<TrophyInfo>();
    silverTrophyInfos: Array<TrophyInfo> = new Array<TrophyInfo>();
    goldTrophyInfos: Array<TrophyInfo> = new Array<TrophyInfo>();
    platinumTrophyInfo: Array<TrophyInfo> = new Array<TrophyInfo>();

    constructor(){
      this.getAllRankTrophyDataFromNet();
    }

    private getAllRankTrophyDataFromNet(){
      axios.get(NET_URL + 'collection/trophy/', {
      }).then((response) => {
        this.parseAllTrophyData(response.data);
      }).catch(function (error) {
        //TODO: エラー処理書く
      });    
    }

    private parseAllTrophyData(html: string){
      var parseHTML = $.parseHTML(html);
  
      ["Normal", "Silver", "Gold", "Platinum"].forEach((value, index, array) => {
        var trophyArray = [];
  
        var $listDiv = $(parseHTML).find("#" + value + "List");
        $listDiv.find(".m_10").each((key, v) => {
          var trophy = new TrophyInfo(
            $($(v).find(".f_14")).text(),
            $($(v).find(".detailText")).text()
          );
          switch (value) {
            case "Normal":    this.normalTrophyInfos.push(trophy);  break;
            case "Silver":    this.silverTrophyInfos.push(trophy);  break;
            case "Gold":      this.goldTrophyInfos.push(trophy);    break;
            case "Platinum":  this.platinumTrophyInfo.push(trophy); break;
          }
        });
     });
    }
  }

  class CharacterFriendlyData{
    friendly: { [key: string]: number} = {};
    constructor(){
      this.getCharacterFriendlyDataFromNet();
    }

    private getCharacterFriendlyDataFromNet(){
      axios.get(NET_URL + 'character/', {
      }).then((response) => {
        this.parseCharacterFriendlyData(response.data);
      }).catch(function (error) {
        //TODO: エラー処理書く
      });
    }

    private parseCharacterFriendlyData(html: string) {
      var parseHTML = $.parseHTML(html);
      var $chara_btn = $(parseHTML).find(".chara_btn");
      $chara_btn.each((key, value) => {
        var characterID: string = $(value).find("input").val() as string || "";
  
        var friendlyTensPlace: string = ($(value).find(".character_friendly_conainer").find("img").eq(1).attr('src') || "0").replace("https://ongeki-net.com/ongeki-mobile/img/friendly/num_", "").replace("0.png", "") ;
        var friendlyUnitsPlace: string = ($(value).find(".character_friendly_conainer").find("img").eq(2).attr('src') || "0").replace("https://ongeki-net.com/ongeki-mobile/img/friendly/num_", "").replace(".png", "");
  
        this.friendly[characterID] = +(friendlyTensPlace + friendlyUnitsPlace);
      });
    }
  }

  class RecentMusicInfo{
    title: string = "";
    technicalScore: number = 0;

    constructor(title: string, technicalScore: number){
      this.title = title;
      this.technicalScore = technicalScore;
    }
  }

  class RatingRecentMusicData{
    ratingRecentMusicObject: Array<RecentMusicInfo> = new Array<RecentMusicInfo>();

    constructor(){
      this.getRatingRecentMusicDataFromNet();
    }

    private getRatingRecentMusicDataFromNet() {
      axios.get(NET_URL + 'home/ratingTargetMusic/', {
      }).then((response) => {
        this.parseRatingRecentMusicData(response.data);
      }).catch(function (error) {
        //TODO: エラー処理書く
      }); 
    }

    private parseRatingRecentMusicData(html: string) {
      var parseHTML = $.parseHTML(html);
      var $basic_btn = $(parseHTML).find(".basic_btn");
      var count: number = 0;
  
      $basic_btn.each((key, value) => {
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

  var playerData: PlayerData = new PlayerData();
  var scoreData: ScoreData = new ScoreData();
  var trophyData: TrophyData= new TrophyData();
  var characterFriendlyData: CharacterFriendlyData = new CharacterFriendlyData();
  var ratingRecentMusicData: RatingRecentMusicData = new RatingRecentMusicData();

  console.log(playerData);
  console.log(scoreData);
  console.log(trophyData);
  console.log(characterFriendlyData);
  console.log(ratingRecentMusicData);

  var allData = {
    player: playerData,
    score: scoreData,
    trophy: trophyData,
    character: characterFriendlyData,
    recent: ratingRecentMusicData,
  }
  var json = JSON.stringify(allData);

  console.log(allData);
  console.log(json);
})();
