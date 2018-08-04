// import axios from  'axios'だと通らない・・・ なんで・・・
import axios from '../node_modules/axios/index';

(function () {
  const NET_URL = "https://ongeki-net.com/ongeki-mobile/";

  const TOOL_URL = "https://example.net/";

  const DIFFICULTY_LENGTH = 4;

  const PRODUCT_NAME = "Project Primera - getScore";
  const VERSION = 1.0;

  console.log("run");

  function getPlayerDataFromNet() {
    axios.get(NET_URL + 'home/playerDataDetail/', {
    }).then(function (response) {
      parsePlayerData(response.data);
    }).catch(function (error) {
      //TODO: エラー処理書く
    });
  }

  function parsePlayerData(html: string) {
    var parseHTML = $.parseHTML(html);
    playerDataArray['trophy'] = $(parseHTML).find(".trophy_block").find("span").text();
    playerDataArray['level'] = $(parseHTML).find(".lv_block").find("span").text();
    playerDataArray['name'] = $(parseHTML).find(".name_block").find("span").text();
    playerDataArray['battle_point'] = $(parseHTML).find(".battle_rank_block").find("div").text().replace(/,/g, "");
    playerDataArray['rating'] = $(parseHTML).find(".rating_block").find(".rating_field").find("[class^='rating_']").eq(0).text();
    playerDataArray['rating_max'] = $(parseHTML).find(".rating_block").find(".rating_field").find(".f_11").text().replace(/（MAX /g, "").replace(/）/g, "");
    playerDataArray['money'] = $(parseHTML).find(".user_data_detail_block").find("td").eq(2).text().split("（")[0].replace(/,/g, "");
    playerDataArray['total_money'] = $(parseHTML).find(".user_data_detail_block").find("td").eq(2).text().split("（")[1].replace(/累計 /g, "").replace(/）/g, "").replace(/,/g, "");
    playerDataArray['total_play'] = $(parseHTML).find(".user_data_detail_block").find("td").eq(5).text();
    playerDataArray['comment'] = $(parseHTML).find(".comment_block").parent().text().replace(/	/g, "").replace("\n", "").replace("\n", "");
  }

  function getAllDifficultyScoreDataFromNet(){
    [0, 1, 2, 3, 10].forEach(function(value, index, array){
      getScoreHtmlFromNet(value);
    });
  }
  
  function getScoreHtmlFromNet(difficulty: number){
    axios.get(NET_URL + 'record/musicGenre/search/', {
      params: {
        genre: 99,
        diff: difficulty
      }
    }).then(function (response) {
      parseScoreData(response.data, difficulty);
    }).catch(function (error) {
      //TODO: エラー処理書く
    });
  }

  function parseScoreData(html: string, difficulty: number) {
    var parseHTML = $.parseHTML(html);
    var $innerContainer3 = $(parseHTML).find(".basic_btn");
    var scoreArray = [];

    $innerContainer3.each(function (key, value) {
      $(value).each(function (k, v) {
        var songTitle = $(v).find(".music_label").text();
        var overDamageHighScore = $($(v).find(".score_value")[0]).text();
        var battleHighScore = $($(v).find(".score_value")[1]).text();
        var technicalHighScore = $($(v).find(".score_value")[2]).text();
        scoreArray[songTitle] = {
          OverDamageHighScore: overDamageHighScore,
          BattleHighScore: battleHighScore,
          TechnicalHighScore: technicalHighScore,
        }
      });
    });
    scoreDataArray[difficulty] = scoreArray;
  }

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
      trophyDataArray[value] = trophyArray;
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

      characterFriendlyDataArray[characterID] = friendlyTensPlace + friendlyUnitsPlace;
    });

    playerDataArray['comment'] = $(parseHTML).find(".comment_block").parent().text().replace(/	/g, "").replace("\n", "").replace("\n", "");
  }

  var playerDataArray = [];
  var scoreDataArray = [[]];
  var trophyDataArray = [[]];
  var characterFriendlyDataArray = [];

  getPlayerDataFromNet();
  getAllDifficultyScoreDataFromNet();
  getAllRankTrophyDataFromNet();
  getCharacterFriendlyDataFromNet();
  
  console.log(playerDataArray);
  console.log(scoreDataArray);
  console.log(trophyDataArray);
  console.log(characterFriendlyDataArray);
})();
