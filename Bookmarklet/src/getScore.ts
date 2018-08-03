// import axios from  'axios'だと通らない・・・ なんで・・・
import axios from './../node_modules/axios/lib/axios.js';

(function () {
  var NET_URL = "https://ongeki-net.com/ongeki-mobile/";
  var NET_MUSICGENRE_URL = "https://ongeki-net.com/ongeki-mobile/record/musicGenre/";

  var TOOL_URL = "https://example.net/";

  var PRODUCT_NAME = "Project Primera - getScore";
  var VERSION = 1.0;

  console.log("run");

  axios.get(NET_MUSICGENRE_URL + 'search/', {
    params: {
      genre: 99,
      diff: 1
    }
  })
  .then(function (response) {
    parseScoreData(response.data);
    // console.log(response.data);
  })
  .catch(function (error) {
    console.log(error);
  });

  function parseScoreData(html: string) {
    var scoreDataArray = [];

    var parseHTML = $.parseHTML(html);
    var $innerContainer3 = $(parseHTML).find(".basic_btn");

    $innerContainer3.each(function (key, value) {
      $(value).each(function (k, v) {
        var songTitle = $(v).find(".music_label").text();
        var overDamageHighScore = $($(v).find(".score_value")[0]).text();
        var battleHighScore = $($(v).find(".score_value")[1]).text();
        var technicalHighScore = $($(v).find(".score_value")[2]).text();
        scoreDataArray[songTitle] = {
          OverDamageHighScore: overDamageHighScore,
          BattleHighScore: battleHighScore,
          TechnicalHighScore: technicalHighScore,
        }
      });
    });

    console.log(scoreDataArray);
  }

})();
