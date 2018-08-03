// import axios from  'axios'だと通らない・・・ なんで・・・
import axios from '../node_modules/axios/index';

(function () {
  const NET_URL = "https://ongeki-net.com/ongeki-mobile/";
  const NET_MUSICGENRE_URL = "https://ongeki-net.com/ongeki-mobile/record/musicGenre/";

  const TOOL_URL = "https://example.net/";

  const DIFFICULTY_LENGTH = 4;

  const PRODUCT_NAME = "Project Primera - getScore";
  const VERSION = 1.0;

  var scoreDataArray = [[]];

  console.log("run");

  function getAllDifficultyScoreDataFromNet(){
    for (let i = 0; i <= DIFFICULTY_LENGTH; ++i) {
      getScoreHtmlFromNet(i)
    }
  }
  
  function getScoreHtmlFromNet(difficulty: number){
    axios.get(NET_MUSICGENRE_URL + 'search/', {
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
    var array = [];

    $innerContainer3.each(function (key, value) {
      $(value).each(function (k, v) {
        var songTitle = $(v).find(".music_label").text();
        var overDamageHighScore = $($(v).find(".score_value")[0]).text();
        var battleHighScore = $($(v).find(".score_value")[1]).text();
        var technicalHighScore = $($(v).find(".score_value")[2]).text();
        array[songTitle] = {
          OverDamageHighScore: overDamageHighScore,
          BattleHighScore: battleHighScore,
          TechnicalHighScore: technicalHighScore,
        }
      });
    });

    scoreDataArray[difficulty] = array;

  }

  getAllDifficultyScoreDataFromNet();
  console.log(scoreDataArray);
})();
