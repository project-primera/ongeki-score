// import axios from  'axios'だと通らない・・・ なんで・・・
import axios from '../node_modules/axios/index';

(function () {
  const NET_URL = "https://ongeki-net.com/ongeki-mobile/";

  const TOOL_URL = "https://example.net/";

  const DIFFICULTY_LENGTH = 4;

  const PRODUCT_NAME = "Project Primera - getScore";
  const VERSION = 1.0;

  var scoreDataArray = [[]];
  var trophyDataArray = [[]];

  console.log("run");

  function getAllDifficultyScoreDataFromNet(){
    for (let i = 0; i <= DIFFICULTY_LENGTH; ++i) {
      getScoreHtmlFromNet(i)
    }
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

  getAllDifficultyScoreDataFromNet();
  getAllRankTrophyDataFromNet();
  
  console.log(scoreDataArray);
  console.log(trophyDataArray);
})();
