const translate = require('@iamtraction/google-translate');
const http = require('http');
const url = require('url');
const concat = require('concat-stream');
const qs = require('querystring');

// Translator
// translate('Осталось неизвестным, когда точно появился первый виноград, но по результатам исследований очевидно, что за 4000 лет до н.э. его уже культивировали древние египтяне. От них виноградная лоза перешла к финикийцам, которых долгое время считали первооткрывателями винограда, затем к грекам, от греков — к римлянам и далее по всему миру. Когда греки перенесли культуру винограда в Италию в Римском государстве виноград почти вытеснил зерновые культуры, и зерно для питания населения пришлось завозить из других стран. Историки свидетельствуют: в римском государстве было такое изобилие виноградников, что Ганнибал поил вином лошадей, а для торжественных празднеств римляне наливали вино в меха необыкновенных размеров. Так, на одном из пиров Птоломея Филадельфа был подан наполненный вином мех из шкур пантер высотой в 12 метров и шириной около 7 метров… Из Рима виноградная лоза проникла в Испанию, Венгрию и Францию, где виноделие достигло такого расцвета, что почти затмило искусство римских мастеров. Там научились делать вина, отличающиеся тонким вкусом и особым ароматом. 3500 лет назад виноградарством и виноделием уже славились Месопотамия, Ассирия, Вавилон, несколько позже — Армения.', { from: 'ru', to: 'en' }).then(res => {
//     console.log(res.text); // OUTPUT: Je vous remercie
//     console.log(res.from.autoCorrected); // OUTPUT: true
//     console.log(res.from.text.value); // OUTPUT: [Thank] you
//     console.log(res.from.text.didYouMean); // OUTPUT: false
// }).catch(err => {
//     console.error(err);
// });


// Server.
const server = http.createServer((request, response) => {
  if (request.method == 'POST') {
    var body = '';

    request.on('data', function (data) {
      body += data;

      // Too much POST data, kill the connection!
      // 1e6 === 1 * Math.pow(10, 6) === 1 * 1000000 ~~~ 1MB
      if (body.length > 1e6)
        request.connection.destroy();
    });

    request.on('end', function () {
      var post = qs.parse(body);
      console.log(post, 'post');
      console.log(post.translateText);
      console.log(post.translateFrom);
      console.log(post.translateTo);

      if (post.translateText == undefined || post.translateFrom == undefined || post.translateTo == undefined) {
        response.writeHead(401, 'Wrong data');
        response.end('Wrong data');
      }

      translate(post.translateText, {
        from: post.translateFrom,
        to: post.translateTo
      }).then(translateResponse => {
        console.log(translateResponse.text, 'output'); // OUTPUT: Je vous remercie
        console.log(translateResponse.from.autoCorrected, 'auto corrected'); // OUTPUT: true
        console.log(translateResponse.from.text.value, 'from text value'); // OUTPUT: [Thank] you
        console.log(translateResponse.from.text.didYouMean, 'did you mean'); // OUTPUT: false
        response.end(translateResponse.text);
      }).catch(err => {
        response.writeHead(401, err);
        response.end(err);
      });
    });
  } else {
    response.writeHead(401, 'Wrong query method: use POST instead.');
    response.end('Wrong query method: use POST instead.');
  }
})

server.listen(8000, '127.0.0.1', () => {
  console.log('Listening to requests on port 8000');
});
