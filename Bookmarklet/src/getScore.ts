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
/*
  function getAllRankTrophyDataFromNet(){
    axios.get(NET_URL + 'collection/trophy/', {
    }).then(function (response) {
      parseAllTrophyData(response.data);
    }).catch(function (error) {
      //TODO: エラー処理書く
    });    
  }

  function parseAllTrophyData(html: string){
    var parseHTML = $.parseHTML(html);

    ["Normal", "Silver", "Gold", "Platinum"].forEach(function(value, index, array){
      var trophyArray = [];

      var $listDiv = $(parseHTML).find("#" + value + "List");
      $listDiv.find(".m_10").each(function (key, v) {
        var trophyName = $($(v).find(".f_14")).text();
        var trophyDetail = $($(v).find(".detailText")).text();
        trophyArray[trophyName] = trophyDetail;
      })
      trophyDataObject[value] = trophyArray;
   });
  }

  function getCharacterFriendlyDataFromNet(){
    axios.get(NET_URL + 'character/', {
    }).then(function (response) {
      parseCharacterFriendlyData(response.data);
    }).catch(function (error) {
      //TODO: エラー処理書く
    });
  }

  function parseCharacterFriendlyData(html: string) {
    var parseHTML = $.parseHTML(html);
    var $chara_btn = $(parseHTML).find(".chara_btn");
    $chara_btn.each(function (key, value) {
      var characterID = $(value).find("input").val().toString();
      // characterFriendlyDataArray[characterID]

      var friendlyTensPlace = $(value).find(".character_friendly_conainer").find("img").eq(1).attr('src').replace("https://ongeki-net.com/ongeki-mobile/img/friendly/num_", "").replace("0.png", "");
      var friendlyUnitsPlace  = $(value).find(".character_friendly_conainer").find("img").eq(2).attr('src').replace("https://ongeki-net.com/ongeki-mobile/img/friendly/num_", "").replace(".png", "");

      characterFriendlyDataObject[characterID] = friendlyTensPlace + friendlyUnitsPlace;
    });

    playerDataObject['comment'] = $(parseHTML).find(".comment_block").parent().text().replace(/	/g, "").replace("\n", "").replace("\n", "");
  }

  function getRatingRecentMusicDataFromNet() {
    axios.get(NET_URL + 'home/ratingTargetMusic/', {
    }).then(function (response) {
      parseRatingRecentMusicData(response.data);
    }).catch(function (error) {
      //TODO: エラー処理書く
    }); 
  }

  function parseRatingRecentMusicData(html: string) {
    var parseHTML = $.parseHTML(html);
    var $basic_btn = $(parseHTML).find(".basic_btn");
    var count: number = 0;

    $basic_btn.each(function (key, value) {
      if ($(value).html().match(/TECHNICAL SCORE/)) {
        var title = $(value).find(".music_label").text();
        var technicalScore = $(value).find(".score_value").text();
        ratingRecentMusicObject[count++] = {
          title: title,
          technical_score: technicalScore,
        };
      }
    });
  }

  var playerDataObject: Object = {};
  var scoreDataObject: Object = {};
  var trophyDataObject: Object = {};
  var characterFriendlyDataObject: Object = {};
  var ratingRecentMusicObject: Object = {};
  */

  var playerData: PlayerData = new PlayerData();
  var scoreData: ScoreData = new ScoreData();

  console.log(playerData);
  console.log(scoreData);

  /*
  var allData = {
    player: playerDataObject,
    score: scoreDataObject,
    trophy: trophyDataObject,
    character: characterFriendlyDataObject,
    recent: ratingRecentMusicObject,
  }
  var json = JSON.stringify(allData);

  console.log(allData);
  console.log(json);
  */
})();
