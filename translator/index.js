const translate = require('@iamtraction/google-translate');
const http = require('http');
const qs = require('querystring');

// Server.
const server = http.createServer((request, response) => {
  if (request.method == 'POST') {
    var body = '';

    request.on('data', function (data) {
      body += data;

      // Too much POST data, kill the connection!
      // 1e6 === 1 * Math.pow(10, 6) === 1 * 1000000 ~~~ 1MB
      if (body.length > 7e6)
        request.connection.destroy();
    });

    // @todo Setup proxy.
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
        console.log(err);
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
